<?php
namespace Cms\Controller;

use Cms\Controller\AppController;
use Cms\Controller\UploadTrait;
use InvalidArgumentException;

/**
 * Articles Controller
 *
 * @property \Cms\Model\Table\ArticlesTable $Articles
 */
class ArticlesController extends AppController
{
    use UploadTrait;

    /**
     * View method
     *
     * @param string $siteId Site id or slug.
     * @param string $typeId Type slug.
     * @param string|null $id Article id.
     * @return void
     */
    public function view($siteId, $typeId, $id = null)
    {
        $query = $this->Articles->findByIdOrSlug($id, $id)->limit(1)->contain([
            'Sites',
            'Categories',
            'ArticleFeaturedImages' => [
                'sort' => [
                    'created' => 'DESC'
                ]
            ]
        ]);
        $article = $query->firstOrFail();

        $categories = $this->Articles->Categories->find('treeList', [
            'conditions' => ['Categories.site_id' => $article->site->id],
            'spacer' => self::TREE_SPACER
        ]);

        $this->set('type', $typeId);
        $this->set('types', [$typeId => $this->Articles->getTypeOptions($typeId)]);
        $this->set('article', $article);
        $this->set('newArticle', $this->Articles->newEntity());
        $this->set('categories', $categories);
        $this->set('_serialize', ['article']);
    }

    /**
     * Type method
     *
     * @param string $siteId Site id or slug.
     * @param string $typeId Type slug.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function type($siteId, $typeId)
    {
        $site = $this->Articles->getSite($siteId);
        $articles = $this->Articles->find('all', [
            'conditions' => ['Articles.site_id' => $site->id, 'Articles.type' => $typeId],
            'contain' => ['Sites', 'Categories', 'ArticleFeaturedImages']
        ]);
        $categories = $this->Articles->Categories->find('treeList', [
            'conditions' => ['Categories.site_id' => $site->id],
            'spacer' => self::TREE_SPACER
        ]);

        $this->set('type', $typeId);
        $this->set('types', [$typeId => $this->Articles->getTypeOptions($typeId)]);
        $this->set('site', $site);
        $this->set('articles', $articles);
        $this->set('categories', $categories);
        $this->set('article', $this->Articles->newEntity());
        $this->set('_serialize', ['type']);
    }

    /**
     * Add method
     *
     * @param string $siteId Site id or slug
     * @param string $type Site type
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     * @throws \InvalidArgumentException
     */
    public function add($siteId, $type)
    {
        $this->request->allowMethod(['post']);

        $typeOptions = $this->Articles->getTypeOptions($type);

        if (empty($typeOptions)) {
            throw new InvalidArgumentException('Unsupported Article type provided.');
        }

        $site = $this->Articles->getSite($siteId);
        $article = $this->Articles->newEntity();

        if ($this->request->is('post')) {
            $data = $this->request->data;
            $data['site_id'] = $site->id;
            $data['type'] = $type;
            $data['created_by'] = $this->Auth->user('id');
            $data['modified_by'] = $this->Auth->user('id');

            $article = $this->Articles->patchEntity($article, $data);
            if ($this->Articles->save($article)) {
                $this->Flash->success(__('The article has been saved.'));
                //Upload the featured image when there is one.
                if ($this->_isValidUpload($this->request->data)) {
                    $this->_upload($article->get('id'));
                }

                return $this->redirect(['action' => 'type', $site->slug, $type]);
            } else {
                $this->Flash->error(__('The article could not be saved. Please, try again.'));
            }
        }
        $categories = $this->Articles->Categories->find('treeList', [
            'conditions' => ['Categories.site_id' => $site->id],
            'spacer' => self::TREE_SPACER
        ]);

        $this->set('typeOptions', $typeOptions);
        $this->set(compact('article', 'categories', 'site'));
        $this->set('_serialize', ['article']);
    }

    /**
     * Edit method
     *
     * @param string $siteId Site id or slug.
     * @param string $type Site type.
     * @param string|null $id Article id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \InvalidArgumentException
     */
    public function edit($siteId, $type, $id = null)
    {
        $this->request->allowMethod(['patch', 'post', 'put']);

        $typeOptions = $this->Articles->getTypeOptions($type);

        if (empty($typeOptions)) {
            throw new InvalidArgumentException('Unsupported Article type provided.');
        }

        $site = $this->Articles->getSite($siteId);
        $query = $this->Articles->findByIdOrSlug($id, $id)->limit(1)->contain([
            'Categories',
            'ArticleFeaturedImages' => [
                'sort' => [
                    'created' => 'DESC'
                ]
            ]
        ]);
        $article = $query->firstOrFail();

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->data;
            $data['site_id'] = $site->id;
            $data['type'] = $type;
            $data['modified_by'] = $this->Auth->user('id');
            $article = $this->Articles->patchEntity($article, $data);
            if ($this->Articles->save($article)) {
                //Upload the featured image when there is one.
                if ($this->_isValidUpload($this->request->data)) {
                    $this->_upload($article->get('id'));
                }
                $this->Flash->success(__('The article has been saved.'));

                return $this->redirect(['action' => 'view', $site->slug, $type, $article->slug]);
            } else {
                $this->Flash->error(__('The article could not be saved. Please, try again.'));
            }
        }

        $categories = $this->Articles->Categories->find('treeList', [
            'conditions' => ['Categories.site_id' => $site->id],
            'spacer' => self::TREE_SPACER
        ]);

        $this->set('typeOptions', $typeOptions);
        $this->set(compact('article', 'categories', 'site'));
        $this->set('_serialize', ['article']);
    }

    /**
     * Delete method
     *
     * @param string $siteId Site id or slug.
     * @param string|null $id Article id.
     * @return \Cake\Network\Response|null Redirects to index.
     */
    public function delete($siteId, $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $query = $this->Articles->findByIdOrSlug($id, $id)->limit(1)->contain('Sites');
        $article = $query->firstOrFail();

        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The article has been deleted.'));
        } else {
            $this->Flash->error(__('The article could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'type', $article->site->slug, $article->type]);
    }

    /**
     * Uploads and stores the related file.
     *
     * @param  int|null $articleId id of the relate slide
     * @return void
     */
    protected function _upload($articleId = null)
    {
        $entity = $this->Articles->ArticleFeaturedImages->newEntity();
        $entity = $this->Articles->ArticleFeaturedImages->patchEntity(
            $entity,
            $this->request->data
        );

        // upload image
        $uploaded = $this->Articles->ArticleFeaturedImages->uploadImage($articleId, $entity);
        if ($uploaded) {
            // delete old image
            $this->Articles->ArticleFeaturedImages->deleteAll([
                'ArticleFeaturedImages.foreign_key' => $articleId,
                'ArticleFeaturedImages.path !=' => $entity->path
            ]);
        }
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
