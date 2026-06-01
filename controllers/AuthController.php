<?php
declare(strict_types=1);

require_once ROOT_PATH . '/models/User.php';

class AuthController extends Controller
{
    public function loginForm(): void
    {
        Security::requireGuest();

        $this->render('auth/login', [
            'pageTitle' => 'Connexion – ' . APP_NAME,
            'error'     => Session::getFlash('error'),
            'success'   => Session::getFlash('success'),
        ]);
    }

    public function login(): void
    {
        Security::verifyCsrf();

        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';

        if ($email === '' || $password === '') {
            Session::flash('error', 'Tous les champs sont obligatoires.');
            $this->redirect('/login');
        }

        // Protection brute-force
        $rateLimitKey = 'login:' . $email . ':' . ($_SERVER['REMOTE_ADDR'] ?? '');
        Security::checkRateLimit($rateLimitKey, 5, 300);

        $userModel = new User();
        $user      = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            Session::flash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('/login');
        }

        if ($user['status'] === 'inactive') {
            Session::flash('error', 'Votre compte est désactivé. Contactez l\'administrateur.');
            $this->redirect('/login');
        }

        session_regenerate_id(true);

        Session::set('user_id',        (int) $user['id']);
        Session::set('user_role',      $user['role']);
        Session::set('user_firstname', $user['firstname']);
        Session::set('user_lastname',  $user['lastname']);
        Session::set('user_status',    $user['status']);

        Security::clearRateLimit('login:' . $email);

        // Redirection sécurisée vers la page initialement demandée
        $fallback = '/dashboard/' . $user['role'];
        $stored   = Session::get('_redirect_after_login', $fallback);
        Session::remove('_redirect_after_login');
        $this->redirect(Security::safeRedirect($stored, $fallback));
    }


    public function registerForm(): void
    {
        Security::requireGuest();

        $this->render('auth/register', [
            'pageTitle' => 'Inscription – ' . APP_NAME,
            'error'     => Session::getFlash('error'),
            'success'   => Session::getFlash('success'),
        ]);
    }

    public function register(): void
    {
        Security::verifyCsrf();

        $firstname = trim($_POST['firstname']        ?? '');
        $lastname  = trim($_POST['lastname']         ?? '');
        $email     = trim($_POST['email']            ?? '');
        $password  = $_POST['password']              ?? '';
        $confirm   = $_POST['password_confirm']      ?? '';
        $role      = $_POST['role']                  ?? 'client';

        if (!in_array($role, ['client', 'owner'], true)) {
            $role = 'client';
        }

        if ($firstname === '' || $lastname === '' || $email === '' || $password === '') {
            Session::flash('error', 'Tous les champs sont obligatoires.');
            $this->redirect('/register');
        }

        if (mb_strlen($firstname) > 50 || mb_strlen($lastname) > 50 || mb_strlen($email) > 150) {
            Session::flash('error', 'Une ou plusieurs valeurs dépassent la taille autorisée.');
            $this->redirect('/register');
        }

        if (!preg_match('/^[\p{L}\s\-\']{1,50}$/u', $firstname)
            || !preg_match('/^[\p{L}\s\-\']{1,50}$/u', $lastname)) {
            Session::flash('error', 'Le nom ne doit contenir que des lettres.');
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Adresse email invalide.');
            $this->redirect('/register');
        }

        if (strlen($password) < 8) {
            Session::flash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
            $this->redirect('/register');
        }

        if ($password !== $confirm) {
            Session::flash('error', 'Les mots de passe ne correspondent pas.');
            $this->redirect('/register');
        }

        $userModel = new User();

        if ($userModel->emailExists($email)) {
            Session::flash('error', 'Cette adresse email est déjà utilisée.');
            $this->redirect('/register');
        }

        $userModel->create($firstname, $lastname, $email, $password, $role);

        Session::flash('success', 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.');
        $this->redirect('/login');
    }


    public function logout(): void
    {
        session_regenerate_id(true);
        Session::destroy();
        header('Location: ' . APP_URL . '/login');
        exit;
    }


    public function profileForm(): void
    {
        Security::requireLogin();

        $user = (new User())->findById(Session::userId());

        $this->render('auth/profile', [
            'pageTitle'   => 'Mon profil – ' . APP_NAME,
            'user'        => $user,
            'error'       => Session::getFlash('error'),
            'success'     => Session::getFlash('success'),
            'errorPwd'    => Session::getFlash('error_pwd'),
            'successPwd'  => Session::getFlash('success_pwd'),
        ]);
    }

    public function updateProfile(): void
    {
        Security::requireLogin();
        Security::verifyCsrf();

        $firstname = trim($_POST['firstname'] ?? '');
        $lastname  = trim($_POST['lastname']  ?? '');
        $email     = trim($_POST['email']     ?? '');

        if ($firstname === '' || $lastname === '' || $email === '') {
            Session::flash('error', 'Tous les champs sont obligatoires.');
            $this->redirect('/profile');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Adresse email invalide.');
            $this->redirect('/profile');
        }

        $userModel = new User();

        if ($userModel->emailExists($email, Session::userId())) {
            Session::flash('error', 'Cette adresse email est déjà utilisée par un autre compte.');
            $this->redirect('/profile');
        }

        $userModel->updateProfile(Session::userId(), $firstname, $lastname, $email);

        Session::set('user_firstname', $firstname);
        Session::set('user_lastname',  $lastname);

        Session::flash('success', 'Profil mis à jour avec succès.');
        $this->redirect('/profile');
    }

    public function changePassword(): void
    {
        Security::requireLogin();
        Security::verifyCsrf();

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($current === '' || $new === '' || $confirm === '') {
            Session::flash('error_pwd', 'Tous les champs sont obligatoires.');
            $this->redirect('/profile');
        }

        if ($new !== $confirm) {
            Session::flash('error_pwd', 'Les nouveaux mots de passe ne correspondent pas.');
            $this->redirect('/profile');
        }

        $validationError = User::validatePassword($new);
        if ($validationError !== null) {
            Session::flash('error_pwd', $validationError);
            $this->redirect('/profile');
        }

        $userModel = new User();

        if (!$userModel->verifyPassword(Session::userId(), $current)) {
            Session::flash('error_pwd', 'Mot de passe actuel incorrect.');
            $this->redirect('/profile');
        }

        $userModel->changePassword(Session::userId(), $new);

        session_regenerate_id(true);

        Session::flash('success_pwd', 'Mot de passe modifié avec succès.');
        $this->redirect('/profile');
    }
}
