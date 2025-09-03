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

    public function rules()
    {
        return [
            [['username', 'department', 'topic'], 'safe'],
            [['status'], 'integer'],
        ];
    }

    public function search($params)
    {
        $query = TicketHead::find()->joinWith(['userName u']); // alias 'u' for user

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
            ->andFilterWhere(['like', 'ticket_head.department', $this->department])
            ->andFilterWhere(['like', 'ticket_head.topic', $this->topic]);

        if ($this->status !== null && $this->status !== '') {
            $query->andWhere(['ticket_head.status' => (int)$this->status]);
        }

        return $dataProvider;
    }
}
