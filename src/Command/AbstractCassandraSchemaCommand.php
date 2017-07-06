<?php

namespace Fitatu\Cassandra\Command;

use Fitatu\Cassandra\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author    Sebastian Szczepański
 * @copyright Fitatu Sp. z o.o.
 */
abstract class AbstractCassandraSchemaCommand extends Command
{
    const COMMAND_BASE = 'fitatu:cassandra:';
    const COMMAND = 'schema:create';

    const DESCRIPTION = 'Generate Cassandra Database Table Schema';
    const COMMAND_TITLE = '';

    /**
     * @var QueryBuilder
     */
    protected $cassandra;

    /**
     * @param QueryBuilder $cassandra
     */
    public function __construct(QueryBuilder $cassandra)
    {
        parent::__construct(static::COMMAND);
        $this->cassandra = $cassandra;
    }

    protected function configure()
    {
        $this->setName(static::COMMAND_BASE.static::COMMAND)
            ->setDescription(static::DESCRIPTION);
    }

    /**
     * @return string
     */
    public function getCommandTitle(): string
    {
        if (!empty(static::COMMAND_TITLE)) {
            return static::COMMAND_TITLE;
        }

        return static::COMMAND;
    }

    /**
     * @example
     *  $this->cassandra
     *       ->addTable('tableName)
     *       ->setPrimaryKey('id')
     *       ->withFields([ 'name' => 'type' ])
     *       ->persist();
     *
     * @return void
     */
    abstract public function createTable();

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $io->title($this->getCommandTitle());

            $this->createTable();

            $io->writeln('Database table has been created.');
        } catch (\Exception $e) {
            $io->writeln($e->getMessage());
        }

        return 0;
    }
}