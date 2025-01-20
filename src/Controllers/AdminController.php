<?php
namespace Controllers;

use Core\Controller;
use Core\Session;
use Models\User;

class AdminController extends Controller {
    private User $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        
        // Seul le super_admin a accès
        if (!$this->checkPermission('*')) {
            return;
        }
    }

    public function index() {
        $stats = [
            'users_count' => $this->userModel->count(),
            'recent_users' => $this->userModel->getRecent(5)
        ];

        $this->view->assign('stats', $stats);
        $this->view->render('admin/index');
    }

    public function users() {
        $users = $this->userModel->all();
        $this->view->assign('users', $users);
        $this->view->render('admin/users/index');
    }

    public function createUser() {
        $roles = ['pastor', 'treasurer', 'secretary'];
        $this->view->assign('roles', $roles);
        $this->view->render('admin/users/create');
    }

    public function storeUser() {
        $data = [
            'username' => $this->getPost('username'),
            'email' => $this->getPost('email'),
            'password' => $this->getPost('password'),
            'role' => $this->getPost('role'),
            'status' => 1
        ];

        $errors = $this->validate($data, [
            'username' => 'required|min:3|max:50',
            'email' => 'required|email|max:100',
            'password' => 'required|min:8',
            'role' => 'required'
        ]);

        if (!empty($errors)) {
            Session::setFlash('danger', 'Veuillez corriger les erreurs');
            $this->view->assign('errors', $errors);
            $this->view->assign('data', $data);
            $this->createUser();
            return;
        }

        // Hash du mot de passe
        $data['password'] = User::hashPassword($data['password']);

        if ($this->userModel->create($data)) {
            Session::setFlash('success', 'Utilisateur créé avec succès');
            $this->redirect('/admin/users');
        } else {
            Session::setFlash('danger', 'Erreur lors de la création de l\'utilisateur');
            $this->view->assign('data', $data);
            $this->createUser();
        }
    }

    public function editUser(int $id) {
        $user = $this->userModel->find($id);
        if (!$user) {
            Session::setFlash('danger', 'Utilisateur non trouvé');
            $this->redirect('/admin/users');
        }

        $roles = ['pastor', 'treasurer', 'secretary'];
        $this->view->assign('roles', $roles);
        $this->view->assign('user', $user);
        $this->view->render('admin/users/edit');
    }

    public function updateUser(int $id) {
        $user = $this->userModel->find($id);
        if (!$user) {
            Session::setFlash('danger', 'Utilisateur non trouvé');
            $this->redirect('/admin/users');
        }

        $data = [
            'username' => $this->getPost('username'),
            'email' => $this->getPost('email'),
            'role' => $this->getPost('role'),
            'status' => (int)$this->getPost('status')
        ];

        // Mise à jour du mot de passe uniquement si fourni
        if ($password = $this->getPost('password')) {
            $data['password'] = User::hashPassword($password);
        }

        $errors = $this->validate($data, [
            'username' => 'required|min:3|max:50',
            'email' => 'required|email|max:100',
            'role' => 'required'
        ]);

        if (!empty($errors)) {
            Session::setFlash('danger', 'Veuillez corriger les erreurs');
            $this->view->assign('errors', $errors);
            $this->view->assign('data', $data);
            $this->editUser($id);
            return;
        }

        if ($this->userModel->update($id, $data)) {
            Session::setFlash('success', 'Utilisateur mis à jour avec succès');
            $this->redirect('/admin/users');
        } else {
            Session::setFlash('danger', 'Erreur lors de la mise à jour de l\'utilisateur');
            $this->view->assign('data', $data);
            $this->editUser($id);
        }
    }

    public function deleteUser(int $id) {
        $user = $this->userModel->find($id);
        if (!$user) {
            Session::setFlash('danger', 'Utilisateur non trouvé');
            $this->redirect('/admin/users');
        }

        // Empêche la suppression du super_admin
        if ($user['role'] === 'super_admin') {
            Session::setFlash('danger', 'Impossible de supprimer le super admin');
            $this->redirect('/admin/users');
        }

        if ($this->userModel->delete($id)) {
            Session::setFlash('success', 'Utilisateur supprimé avec succès');
        } else {
            Session::setFlash('danger', 'Erreur lors de la suppression de l\'utilisateur');
        }

        $this->redirect('/admin/users');
    }
}
