<?php
/**
 * AnimeDb package
 *
 * @package   AnimeDb
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDb\Bundle\MyAnimeListSyncBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140220123416_AddMalItemTable extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE my_anime_list_item (
            mal_item_id INTEGER NOT NULL,
            item_id INTEGER NOT NULL,
            PRIMARY KEY(mal_item_id)
        )');
        $this->addSql('CREATE UNIQUE INDEX my_anime_list_item_item_idx ON my_anime_list_item (item_id)');
        $this->addSql('CREATE UNIQUE INDEX my_anime_list_item_idx ON my_anime_list_item (mal_item_id, item_id)');
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('my_anime_list_item');
    }
}