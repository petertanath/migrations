<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\DBAL\Migrations\Tests\Provider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Provider\OrmSchemaProvider;
use Doctrine\DBAL\Migrations\Tests\MigrationTestCase;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Tools\Setup;

/**
 * Tests the OrmSchemaProvider using a real entity manager.
 *
 * @since   1.0.0-alpha3
 */
class OrmSchemaProviderTest extends MigrationTestCase
{
    /** @var  Connection */
    private $conn;

    /** @var  Configuration */
    private $config;

    /** @var  EntityManagerInterface */
    private $entityManager;

    /** @var  OrmSchemaProvider */
    private $ormProvider;

    public function testCreateSchemaFetchesMetadataFromEntityManager()
    {
        $schema = $this->ormProvider->createSchema();
        self::assertInstanceOf(Schema::class, $schema);
        self::assertTrue($schema->hasTable('sample_entity'));
        $table = $schema->getTable('sample_entity');
        self::assertTrue($table->hasColumn('id'));
    }

    public function testEntityManagerWithoutMetadataCausesError()
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->config->setMetadataDriverImpl(new XmlDriver([]));

        $this->ormProvider->createSchema();
    }

    public function notEntityManagers()
    {
        return [
            [new \stdClass],
            [false],
            [1],
            ['oops'],
            [1.0],
        ];
    }

    /**
     * @dataProvider notEntityManagers
     */
    public function testPassingAnInvalidEntityManagerToConstructorCausesError($em)
    {
        $this->expectException(\InvalidArgumentException::class);

        new OrmSchemaProvider($em);
    }

    protected function setUp()
    {
        $this->conn          = $this->getSqliteConnection();
        $this->config        = Setup::createXMLMetadataConfiguration([__DIR__ . '/_files'], true);
        $this->entityManager = EntityManager::create($this->conn, $this->config);
        $this->ormProvider   = new OrmSchemaProvider($this->entityManager);
    }
}
