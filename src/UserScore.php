<?php

namespace Vgsite;

class UserScore
{
    private $user;
    private $pdo;
    private $logger;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->data = static::getScoreByUserId($user->getId());

        $registry = Registry::instance();
        $this->pdo = $registry->get('pdo');
        $this->logger = $registry->get('logger');

        return $this;
    }

    public static function getScoreByUserId(int $user_id)
    {
        $sql = "SELECT * FROM users_data WHERE user_id=? ORDER BY `date` DESC LIMIT 1";
        $statement = Registry::instance()->get('pdo')->prepare($sql);
        $statement->execute([$user_id]);
        if (!$row = $statement->fetch()) {
            return null;
        }
        
        return $row;
    }
    
    public function calculateScore(): array
    {        
        $queries = array(
            'num_forumposts' => sprintf("SELECT COUNT(*) FROM forums_posts WHERE user_id=%d", $this->user->getId()),
            'num_pageedits' => sprintf("SELECT COUNT(*) FROM pages_edit WHERE user_id=%d AND published=1", $this->user->getId()),
            'num_ps' => sprintf("SELECT COUNT(*) FROM `pages` WHERE redirect_to = '' AND (`contributors` = '[%d]' OR `contributors` LIKE '[%d,%%')", $this->user->getId(), $this->user->getId()),
            'num_ps_stolen' => "SELECT COUNT(*) FROM `pages_edit` WHERE redirect_to = '' AND new_ps='1'",
            'num_sblogposts' => sprintf("SELECT COUNT(*) FROM `posts` WHERE `user_id`=%d AND category != 'draft' AND pending != '1'", $this->user->getId()),
        );

        foreach($queries as $key => $sql) {
            $statement = $this->pdo->query($sql);
            $this->data[$key] = $statement->fetchColumn();
        }
        
        if ($this->data['num_forumposts']) {
            $this->data['score_forums'] = $this->data['num_forumposts'] * .1 * ($this->data['forum_rating'] > 10 ? $this->data['forum_rating'] * .1 : 1);
        }
        
        if ($this->data['num_sblogposts']) {
            $sblog_multiplier = 1 + (($this->data['sblog_rating'] / $this->data['num_sblogposts']) / 21); // where 1 is no ratings and 2 is all perfect ratings
            $this->data['score_sblogs'] = $this->data['num_sblogposts'] * $sblog_multiplier;
        }
        
        if ($this->data['num_pageedits']) {
            $this->data['score_pages'] = $this->data['contribution_score'] * .1 + ($this->data['num_ps'] * 2);
        }
        
        $this->data['score_total'] = $this->data['score_forums'] + $this->data['score_sblogs'] + $this->data['score_pages'];

        return $this->data;
    }

    public function save(): bool
    {
        if (!$this->data['score_total'] >= 1) {
            $this->calculateScore();
        }

        $this->data['date'] = date("Y-m-d");
        $sql = "INSERT INTO users_data (".implode(", ", array_keys($this->data)).") VALUES 
            ('".implode("', '", array_values($this->data))."');";
        $statement = $this->pdo->prepare($sql);
        return $statement->execute();
    }
}
