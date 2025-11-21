<?php
namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class TicketHeadSearch extends Model
{
    public $username;     // related (userName->username)
    public $department;
    public $topic;
    public $status;
    public $answersCount;

    public function rules()
    {
        return [
            [['username', 'department', 'topic'], 'safe'],
            [['answersCount','status'], 'integer'],
        ];
    }

    public function search($params)
    {
        //$query = TicketHead::find()->joinWith(['userName u']); // alias 'u' for user
        $answersSubquery = TicketBody::find()
            ->select([
                'id_head',
                // pure "admin replies" count:
                'answersCount' => 'SUM(CASE WHEN client = 1 THEN 1 ELSE 0 END)',
            ])
            ->groupBy('id_head');
        $query = TicketHead::find()
            ->alias('th')
            ->joinWith(['userName u']) // existing join for username
            ->leftJoin(['ab' => $answersSubquery], 'ab.id_head = th.id');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => TicketConfig::pageSize],
            'sort' => [
                'defaultOrder' => ['date_update' => SORT_DESC],
                'attributes' => [
                    'department',
                    'topic',
                    'status',
                    'date_update',
                    // allow sorting by related username
                    'username' => [
                        'asc'  => ['u.username' => SORT_ASC],
                        'desc' => ['u.username' => SORT_DESC],
                    ],
                    'answersCount' => [
                        'asc'  => ['answersCount' => SORT_ASC],
                        'desc' => ['answersCount' => SORT_DESC],
                        'default' => SORT_DESC,
                        'label' => 'No of answers',
                    ],
                ],
            ],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        // filters
        $query->andFilterWhere(['like', 'u.username', $this->username])
            ->andFilterWhere(['th.status' => $this->status])
            ->andFilterWhere(['like', 'ticket_head.topic', $this->topic]);
        if ($this->answersCount !== null && $this->answersCount !== '') {
            $query->andWhere(['answersCount' => $this->answersCount]);
        }
        if ($this->status !== null && $this->status !== '') {
            $query->andFilterWhere(['ticket_head.status' => $this->status]);
        }
        return $dataProvider;
    }
}
