<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Network\Exception\UnauthorizedException;
use Cake\Network\Exception\BadRequestException;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\NotFoundException;

/**
 * Reports Controller
 *
 * @property \App\Model\Table\ReportsTable $Reports
 *
 * @method \App\Model\Entity\Report[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ReportsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        try {
            $this->paginate = [
                'contain' => ['Users'],
            ];
            $reports = $this->paginate($this->Reports);
    
            $this->set(compact('reports'));
            $this->set([
                'message' => 'success',
                'data' => $reports,
                '_serialize' => ['message', 'data']
            ]);
        } catch (\Throwable $th) {
            throw new InternalErrorException('Internal Server Error');
        }
    }

    /**
     * View method
     *
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        try {
            $report = $this->Reports->get($id, [
                'contain' => ['Users'],
            ]);
            $this->set('report', $report);
            $this->set([
                'message' => 'success',
                'data' => $report,
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
            $report = $this->Reports->newEntity();
            if ($this->request->is('post')) {
                $report = $this->Reports->patchEntity($report, $this->request->getData());
                if(!$report->title || !$report->description || !$report->user_id) throw new BadRequestException('Invalid input');
                $file = $this->request->getData('image');
                $cloudinaryAPIReq = \Cloudinary\Uploader::upload($file["tmp_name"]);
                $report->image_url = $cloudinaryAPIReq['url'];
                if ($this->Reports->save($report)) {
                    $this->set([
                        'message' => 'The report has been saved.',
                        'data' => $report,
                        '_serialize' => ['message', 'data']
                    ]);
                    return;
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
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        try {
            $report = $this->Reports->get($id, [
                'contain' => [],
            ]);
            if ($this->request->is(['patch', 'post', 'put'])) {
                $report = $this->Reports->patchEntity($report, $this->request->getData());
                if ($this->Reports->save($report)) {
                    $this->set([
                        'message' => 'The report has been saved.',
                        'data' => $report,
                        '_serialize' => ['message', 'data']
                    ]);
                }
            }
        } catch (\Throwable $th) {
            throw new InternalErrorException('Internal Sever Error');
        }
    }

    /**
     * Delete method
     *
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        try {
            $this->request->allowMethod(['post', 'delete']);
            $user_id = $this->Auth->user('id');
            $report = $this->Reports->find()->where([
                'id' => $id,
                'user_id' => $user_id
            ])->first();
            if(!$report) throw new NotFoundException("Record could not be found, or doesn't belong to you");
            
            if ($this->Reports->delete($report)) {
            $this->set([
                'message' => 'The report has been deleted.',
                '_serialize' => ['message']
            ]);
            } else throw new InternalErrorException('Internal Sever Error');
        } catch (\Throwable $th) {
            throw new InternalErrorException('Internal Sever Error');
        }
    }
}
