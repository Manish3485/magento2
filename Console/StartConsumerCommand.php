<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Amqp\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Amqp\ConsumerFactory;

/**
 * Command for starting AMQP consumers.
 */
class StartConsumerCommand extends Command
{
    const ARGUMENT_CONSUMER = 'consumer';
    const OPTION_NUMBER_OF_MESSAGES = 'max-messages';
    const OPTION_DURABLE = 'durable';
    const COMMAND_QUEUE_CONSUMERS_START = 'queue:consumers:start';

    /**
     * @var ConsumerFactory
     */
    private $consumerFactory;

    /**
     * {@inheritdoc}
     *
     * @param ConsumerFactory $consumerFactory
     */
    public function __construct(ConsumerFactory $consumerFactory, $name = null)
    {
        $this->consumerFactory = $consumerFactory;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consumerName = $input->getArgument(self::ARGUMENT_CONSUMER);
        $numberOfMessages = $input->getOption(self::OPTION_NUMBER_OF_MESSAGES);
        $durable = $input->hasOption(self::OPTION_DURABLE);
        $consumer = $this->consumerFactory->get($consumerName);
        $consumer->process($numberOfMessages, $durable);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_QUEUE_CONSUMERS_START);
        $this->setDescription('Start AMQP consumer.');
        $this->addArgument(
            self::ARGUMENT_CONSUMER,
            InputArgument::REQUIRED,
            'The name of the consumer to be started.'
        );
        $this->addOption(
            self::OPTION_NUMBER_OF_MESSAGES,
            null,
            InputOption::VALUE_REQUIRED,
            'The number of messages to be processed by the consumer before process termination. '
            . 'If not specify - terminate after processing all queued messages.'
        );
        $this->addOption(
            self::OPTION_DURABLE,
            null,
            InputOption::VALUE_NONE,
            'This option defines, whether this command will run indefinitely or not '
            . 'if number of messages is not defined. If not specify - the command is not durable.'
        );
        $this->setHelp(
            <<<HELP
This command starts AMQP consumer by its name.

To start consumer which will process all queued messages and terminate execution:

      <comment>%command.full_name% some_consumer</comment>

To specify the number of messages which should be processed by consumer before its termination:

    <comment>%command.full_name% some_consumer --max-messages=50</comment>

To specify the command as durable:

    <comment>%command.full_name% some_consumer --durable</comment>
HELP
        );
        parent::configure();
    }
}
