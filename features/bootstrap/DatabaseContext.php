<?php

use Behat\Gherkin\Node\PyStringNode;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpKernel\Profiler\Profiler;

class DatabaseContext extends Context
{
    /**
     * @var array
     */
    private $result;

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var Profiler
     */
    private $profiler;

    /**
     * @param Profiler $profiler
     * @param Registry $doctrine
     */
    public function __construct(Profiler $profiler, Registry $doctrine)
    {
        $this->profiler = $profiler;
        $this->doctrine = $doctrine;
    }

    /**
     * @Then print all database queries
     */
    public function debugIDumpAllQueries()
    {
        $queries = $this->getDbCollector()->getQueries();
        foreach ($queries as $connection => $queriesByConnection) {
            foreach ($queriesByConnection as $query) {
                $this->printDebug('On "'.$connection.'" run "'.$query['sql'].'" using ['.implode(', ', $query['params']).']');
            }
        }
    }

    /**
     * @param string       $connection
     * @param PyStringNode $string
     *
     * @throws \Exception
     *
     * @Then the following query have to be ran on :connection connection:
     */
    public function theFollowingQueryHaveToBeRanOnConnection($connection, PyStringNode $string)
    {
        $queries = $this->getDbCollector()->getQueries();
        if (!array_key_exists($connection, $queries)) {
            throw new \Exception(sprintf('No connection named "%s" found', $connection));
        }

        foreach ($queries[$connection] as $query) {
            if (preg_match('/^'.$this->replaceParameters(trim($string->getRaw())).'$/', $query['sql'])) {
                return;
            }
        }
        throw new \Exception(sprintf(
            'Query was not found in the %d run queries on %s connection',
            count($queries[$connection]),
            $connection
        ));
    }

    /**
     * @param int    $count
     * @param string $connection
     *
     * @throws \Exception
     *
     * @Then :count queries have been run on :connection connection
     */
    public function queriesHaveBeenRunOnConnection($count, $connection)
    {
        $queries = $this->getDbCollector()->getQueries();
        if (!array_key_exists($connection, $queries)) {
            throw new \Exception(sprintf('No connection named "%s" found', $connection));
        }

        if ($count !== ($actualCount = count($queries[$connection]))) {
            throw new \Exception(sprintf(
                '%d queries were executed on %s connection',
                $actualCount,
                $connection
            ));
        }
    }

    /**
     * @param int    $count
     * @param string $type
     * @param string $connection
     *
     * @throws \Exception
     *
     * @Then :count :type queries have been run on :connection connection
     */
    public function typeQueriesHaveBeenRunOnConnection($count, $type, $connection)
    {
        $queries = $this->getDbCollector()->getQueries();
        if (!array_key_exists($connection, $queries)) {
            throw new \Exception(sprintf('No connection named "%s" found', $connection));
        }

        $actualCount = 0;
        foreach ($queries[$connection] as $query) {
            if (preg_match('/^'.$type.'.* /i', $query['sql'])) {
                $actualCount++;
            }
        }

        if ($count !== $actualCount) {
            throw new \Exception(sprintf(
                '%d %s queries were executed on %s connection',
                $actualCount,
                $type,
                $connection
            ));
        }
    }

    /**
     * @param int    $count
     * @param string $connection
     * @param string $table
     *
     * @throws \Exception
     *
     * @Then :count queries have been run on :connection connection on table :table
     */
    public function queriesHaveBeenRunOnConnectionOnTable($count, $connection, $table)
    {
        $queries = $this->getDbCollector()->getQueries();
        if (!array_key_exists($connection, $queries)) {
            throw new \Exception(sprintf('No connection named "%s" found', $connection));
        }

        $actualCount = 0;
        foreach ($queries[$connection] as $query) {
            if (preg_match('/^ '.$table.' /i', $query['sql'])) {
                $actualCount++;
            }
        }

        if ($count !== $actualCount) {
            throw new \Exception(sprintf(
                '%d queries were executed on %s connection on table %s',
                $actualCount,
                $connection,
                $table
            ));
        }
    }

    /**
     * @param int    $count
     * @param string $type
     * @param string $connection
     * @param string $table
     *
     * @throws \Exception
     *
     * @Then :count :type queries have been run on :connection connection on table :table
     */
    public function typeQueriesHaveBeenRunOnConnectionOnTable($count, $type, $connection, $table)
    {
        $queries = $this->getDbCollector()->getQueries();
        if (!array_key_exists($connection, $queries)) {
            throw new \Exception(sprintf('No connection named "%s" found', $connection));
        }

        $actualCount = 0;
        foreach ($queries[$connection] as $query) {
            $regexp = '/^'.$type.'.* (?:FROM|INTO) '.$table.' /i';
            if (strtoupper($type) === 'UPDATE') {
                $regexp = '/^'.$type.' '.$table.' /i';
            }
            if (preg_match($regexp, $query['sql'])) {
                $actualCount++;
            }
        }

        if ($count !== $actualCount) {
            throw new \Exception(sprintf(
                '%d %s queries were executed on %s connection on table %s',
                $actualCount,
                $type,
                $connection,
                $table
            ));
        }
    }

    /**
     * @Then table :table on connection :connection should contain a record with :name equal to :value
     */
    public function tableOnConnectionShouldContainARecordWithNameEqualsToValue($table, $connection, $name, $value)
    {
        $query = sprintf('SELECT COUNT(*) AS total FROM %s WHERE %s="%s"', $table, $name, str_ireplace('"', '\"', $value));
        $result = $this->doctrine->getConnection($connection)->executeQuery($query)->fetch();
        if ((int) $result['total'] !== 1) {
            throw new \Exception(sprintf('Table %s does not contain any record with %s="%s"', $table, $name, str_ireplace('"', '\"', $value)));
        }
    }

    /**
     * @Then table :table on connection :connection should contain :count records
     */
    public function tableOnConnectionShouldContainCountRecords($table, $connection, $count)
    {
        $query = sprintf('SELECT COUNT(*) AS total FROM %s', $table);
        $result = $this->doctrine->getConnection($connection)->executeQuery($query)->fetch();
        if ((int) $result['total'] != $count) {
            throw new \Exception(sprintf('Table %s contains %d records, %d expected', $table, (int) $result['total'], $count));
        }
    }

    /**
     * @param string $sql
     * @param string $connectionName
     *
     * @When I execute the SQL query :sql on :connectionName
     */
    public function executeSqlQuery($sql, $connectionName)
    {
        $sql = $this->replaceParameters($sql);
        $connection = $this->doctrine->getConnection($connectionName);
        $this->result = $connection->fetchAll($sql);
    }

    /**
     * @When print last query result
     */
    public function printLastQueryResult()
    {
        dump($this->result);
    }

    /**
     * @param int $count
     *
     * @Then I have :count query row result
     */
    public function assertIHaveSqlQueryResult($count)
    {
        \PHPUnit_Framework_Assert::assertEquals($count, count($this->result));
    }

    /**
     * @param int          $rowNumber
     * @param PyStringNode $stringNode
     *
     * @throws \Exception
     *
     * @When the SQL query result row :rowNumber should contain values :
     */
    public function sqlQueryResultShouldContainValues($rowNumber, PyStringNode $stringNode)
    {
        $rawString = $this->replaceParameters($stringNode->getRaw());
        $expectedValues = json_decode($rawString, true);
        $result = $this->result[$rowNumber - 1];

        foreach ($expectedValues as $key => $value) {
            if (array_key_exists($key, $result)) {
                \PHPUnit_Framework_Assert::assertEquals(
                    $value,
                    $result[$key],
                    sprintf('Failed asserting that \'%s\' matches expected \'%s\' for \'%s\'', $result[$key], $value, $key)
                );
            } else {
                throw new \Exception(sprintf('column "%s" was not found in query result', $key));
            }
        }
    }

    /**
     * @return DoctrineDataCollector
     *
     * @throws \Exception
     */
    private function getDbCollector()
    {
        $profile = $this->profiler->collect(
            new \Symfony\Component\HttpFoundation\Request(),
            new \Symfony\Component\HttpFoundation\Response()
        );

        if (empty($profile)) {
            throw new \Exception('Profiler may not have been enabled because no profile were found');
        }

        $dbCollector = $profile->getCollector('db');

        if (empty($dbCollector)) {
            throw new \Exception('Cannot find a database collector');
        }

        return $dbCollector;
    }
}
