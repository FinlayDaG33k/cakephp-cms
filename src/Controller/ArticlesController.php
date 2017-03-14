<?php
namespace Cms\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cms\Controller\AppController;
use Cms\Controller\UploadTrait;

/**
 * Articles Controller
 *
 * @property \Cms\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
{
    use UploadTrait;

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $query = $this->Articles->Sites->find('all', ['conditions' => ['Sites.active' => true]]);
        $sites = $query->all();

        $search = $this->request->query('s');
        $articles = $this->Articles->find('all')->order(['Articles.modified' => 'DESC'])->contain([
            'Author', 'Categories', 'Sites', 'ArticleFeaturedImages' => [
                'sort' => ['created' => 'DESC']
            ]
        ]);

        if (!is_null($search)) {
            $articles = $articles->find(
                'search',
                [
                    'fieldNames' => $this->Articles->searchableFields(),
                    'term' => $search,
                ]
            );
            if ($articles->isEmpty()) {
                $this->Flash->set(__d('cms', 'No articles found for the search term: {0}', $search));
            }
        }

        $this->set(compact('articles', 'sites'));
        $this->set('_serialize', ['articles']);
    }

    /**
     * View method
     *
     * @param string $siteId Site id or slug.
     * @param string|null $id Article id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($siteId, $id = null)
    {
        $query = $this->Articles->findByIdOrSlug($id, $id)->limit(1)->contain([
            'Categories',
            'ArticleFeaturedImages' => [
                'sort' => [
                    'created' => 'DESC'
                ]
            ]
        ]);
        $article = $query->first();

        if (empty($article)) {
            throw new RecordNotFoundException('Article not found.');
        }

        $this->set(compact('article'));
        $this->set('_serialize', ['article']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $article = $this->Articles->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $article->created_by = $user['username'];
                $article->modified_by = $user['username'];
            }
            $article = $this->Articles->patchEntity($article, $this->request->data);
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('The article has been saved.'));
                //Upload the featured image when there is one.
                if ($this->_isValidUpload($this->request->data)) {
                    $this->_upload($article->get('id'));
                }

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The article could not be saved. Please, try again.'));
            }
        }
        $this->set([
            'article' => $article,
            'categories' => $this->Articles->Categories->find('treeList', ['spacer' => self::TREE_SPACER]),
        ]);
        $this->set('_serialize', ['article']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Article id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $query = $this->Articles->findByIdOrSlug($id, $id)->limit(1)->contain([
            'Categories',
            'ArticleFeaturedImages' => [
                'sort' => [
                    'created' => 'DESC'
                ]
            ]
        ]);
        $article = $query->first();

        if (empty($article)) {
            throw new RecordNotFoundException('Article not found.');
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $article = $this->Articles->patchEntity($article, $this->request->data);
            if ($this->Articles->save($article)) {
                //Upload the featured image when there is one.
                if ($this->_isValidUpload($this->request->data)) {
                    $this->_upload($article->get('id'));
                }
                $this->Flash->success(__('The article has been saved.'));

                return $this->redirect(['action' => 'edit', $article->get('id')]);
            } else {
                $this->Flash->error(__('The article could not be saved. Please, try again.'));
            }
        }
        $this->set([
            'article' => $article,
            'categories' => $this->Articles->Categories->find('treeList', ['spacer' => self::TREE_SPACER]),
        ]);
        $this->set('_serialize', ['article']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Article id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $query = $this->Articles->findByIdOrSlug($id, $id)->limit(1);
        $article = $query->first();

        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The article has been deleted.'));
        } else {
            $this->Flash->error(__('The article could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }

    /**
     * Uploads and stores the related file.
     *
     * @param  int|null $articleId id of the relate slide
     * @return bool           flag
     */
    protected function _upload($articleId = null)
    {
        $entity = $this->Articles->ArticleFeaturedImages->newEntity();
        $entity = $this->Articles->ArticleFeaturedImages->patchEntity(
            $entity,
            $this->request->data
        );

        if ($this->Articles->ArticleFeaturedImages->uploadImage($articleId, $entity)) {
            $this->Flash->set(__('Upload successful'));

            return true;
        }

        return false;
    }

    /**
     * Uploads the files from the CKeditor.
     *
     * @link http://docs.ckeditor.com/#!/guide/dev_file_upload
     * @param  int|null $articleId id of the relate slide
     * @return void
     */
    public function uploadFromEditor($articleId = null)
    {
        $result = [];
        $this->request->is(['ajax']);
        if (!$this->request->data['upload']['error']) {
            $file = ['file' => $this->request->data['upload']];
            $entity = $this->Articles->ContentImages->newEntity();
            $entity = $this->Articles->ContentImages->patchEntity(
                $entity,
                $file
            );
            if ($this->Articles->ContentImages->uploadImage($articleId, $entity)) {
                $result['uploaded'] = 1;
                $result['url'] = $entity->path;
            }
        } else {
            $result['uploaded'] = 0;
            $result['error']['message'] = __d('cms', 'Failed to upload.');
        }
        $this->set('result', $result);
        $this->set('_serialize', 'result');
    }

    /**
     * Deletes the association and not the record or the physical file.
     *
     * @param  string $id FileStorage Id
     * @return \Cake\Network\Response Redirecting to the referer.
     */
    public function softDeleteFeaturedImage($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $entity = $this->Articles->ArticleFeaturedImages->get($id);
        $entity = $this->Articles->ArticleFeaturedImages->patchEntity($entity, ['foreign_key' => null]);
        if ($this->Articles->ArticleFeaturedImages->save($entity)) {
            $this->Flash->success(__('The featured image has been deleted.'));
        } else {
            $this->Flash->error(__('The featured image could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }
}
