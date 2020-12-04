<?php
namespace App\Controller;

use App\Controller\AppController;

use Cake\Event\Event;
use Cake\Network\Exception\UnauthorizedException;
use Cake\Network\Exception\InternalErrorException;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['add', 'login']);
        
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        try {
            $users = $this->paginate($this->Users);
            $this->set([
                'message' => 'Success',
                'data' => $users,
                '_serialize' => ['message', 'data']
            ]);
        } catch (\Throwable $th) {
            throw new InternalErrorException('Internal Server Error');
        }
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        try {
            $user = $this->Users->get($id, [
                'contain' => ['Reports'],
            ]);
    
            $this->set('user', $user);
            $this->set([
                'message' => 'Success',
                'data' => $user,
                '_serialize' => ['message', 'data']
            ]);
        } catch (\Throwable $th) {
            throw new InternalErrorException('Internal Server Error');
        }
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        try {
            $user = $this->Users->newEntity();
            if ($this->request->is('post')) {
                $user = $this->Users->patchEntity($user, $this->request->getData());
                $saved =$this->Users->save($user);
                if ($saved) {
                    $token = JWT::encode(
                        [
                            'sub' => $saved->id,
                            'username' => $user->username,
                            'exp' =>  time() + 604800
                        ],
                    Security::salt());
    
                    $this->set([
                        'message' => 'Success',
                        'data' => $user,
                        'token' => $token,
                        '_serialize' => ['message', 'token', 'data']
                    ]);
                    return null;
                }
                throw new InternalErrorException('Internal Server Error');
            }
        } catch (\Throwable $th) {
            throw new InternalErrorException('Internal Server Error');
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        try {
            $user = $this->Users->get($id, [
                'contain' => [],
            ]);
            if ($this->request->is(['patch', 'post', 'put'])) {
                $user = $this->Users->patchEntity($user, $this->request->getData());
                if ($this->Users->save($user)) {
                    $this->set([
                        'message' => 'Success',
                        'data' => $user,
                        '_serialize' => ['message', 'data']
                    ]);
                    return;
                }
            }
        } catch (\Throwable $th) {
            throw new InternalErrorException('Internal Server Error');
        }
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        try {
            $this->request->allowMethod(['post', 'delete']);
            $user = $this->Users->get($id);
            if ($this->Users->delete($user)) {
                $this->set([
                    'message' => 'The user has been deleted.',
                    '_serialize' => ['message']
                ]);
            } else {
                $this->set([
                    'message' => 'The user could not be deleted. Please, try again.',
                    '_serialize' => ['message']
                ]);
            }
        } catch (\Throwable $th) {
            throw new InternalErrorException('Internal Server Error');
        }
    }

    public function login()
    {
        try {
              $user = $this->Auth->identify(); 
            if (!$user) {
                throw new UnauthorizedException($user);
            }
    
            $this->set([
                'success' => true,
                'data' => [
                    'token' => JWT::encode([
                        'sub' => $user['id'],
                        'username' => $user['username'],
                        'exp' =>  time() + 604800
                    ],
                    Security::salt())
                ],
                '_serialize' => ['success', 'data']
            ]);
        } catch (\Throwable $th) {
            throw new UnauthorizedException('Invalid username or password');
        }
    }
    public function logout()
    {
        try {
            $token = $this->Auth->logout();
    
            $this->set([
                'message' => $token,
                '_serialize' => ['message']
            ]);
        } catch (\Throwable $th) {
            throw new InternalErrorException('Internal Server Error');
        }
    }
}
