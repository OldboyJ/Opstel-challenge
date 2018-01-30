<?php

use yii\db\Migration;

/**
 * Class m180130_234452_user_table
 */
class m180130_234452_user_table extends Migration
{
    // /**
    //  * @inheritdoc
    //  */
    // public function safeUp()
    // {
    //
    // }
    //
    // /**
    //  * @inheritdoc
    //  */
    // public function safeDown()
    // {
    //     echo "m180130_234452_user_table cannot be reverted.\n";
    //
    //     return false;
    // }


    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
      $this->createTable('user', [
            'id' => $this->primaryKey(11),
            'first_name' => $this->string(50)->notNull(),
            'last_name' => $this->string(50)->notNull(),
            'email' => $this->string(200)->notNull(),
            'username' => $this->string(255)->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'password_reset_token' => $this->string(255)->notNull(),
          ]);
    }

    public function down()
    {
        $this->dropTable('user');

    }

}
