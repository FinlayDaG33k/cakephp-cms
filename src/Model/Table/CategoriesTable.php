<?php
namespace Cms\Model\Table;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cms\Model\Entity\Category;
use DateTime;
use InvalidArgumentException;

/**
 * Categories Model
 *
 * @property \Cake\ORM\Association\BelongsTo $ParentCategories
 * @property \Cake\ORM\Association\HasMany $ChildCategories
 * @property \Cake\ORM\Association\BelongsToMany $Articles
 */
class CategoriesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('categories');
        $this->displayField('name');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Tree');
        $this->addBehavior('Muffin/Slug.Slug');

        $this->belongsTo('Cms.Sites');
        $this->belongsTo('ParentCategories', [
            'className' => 'Cms.Categories',
            'foreignKey' => 'parent_id'
        ]);
        $this->hasMany('ChildCategories', [
            'className' => 'Cms.Categories',
            'foreignKey' => 'parent_id'
        ]);
        $this->hasMany('Cms.Articles', [
            'sort' => ['Articles.publish_date' => 'DESC'],
            'conditions' => ['Articles.publish_date <=' => new DateTime('now')]
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->uuid('id')
            ->allowEmpty('id', 'create');

        $validator
            ->notEmpty('slug')
            ->add('slug', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->requirePresence('site_id', 'create')
            ->notEmpty('site_id');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['slug']));
        $rules->add($rules->isUnique(['name', 'site_id']));
        $rules->add($rules->existsIn(['parent_id'], 'ParentCategories'));

        return $rules;
    }

    /**
     * Fetch and return Site by id or slug.
     *
     * @param string $id Site id or slug.
     * @return \Cake\ORM\Entity
     * @throws \Cake\Datasource\Exception\RecordNotFoundException
     * @throws \InvalidArgumentException
     */
    public function getSite($id)
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Site id or slug cannot be empty.');
        }

        $query = $this->Sites->find('all', [
            'limit' => 1,
            'conditions' => [
                'OR' => [
                    'Sites.id' => $id,
                    'Sites.slug' => $id
                ],
                'active' => true
            ]
        ]);

        $result = $query->first();

        if (empty($result)) {
            throw new RecordNotFoundException('Site not found.');
        }

        return $result;
    }
}
