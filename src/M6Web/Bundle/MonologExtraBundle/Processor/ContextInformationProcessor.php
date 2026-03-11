<?php
namespace M6Web\Bundle\MonologExtraBundle\Processor;

use Monolog\LogRecord;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ContextInformationProcessor
{
    protected ContainerInterface $container;
    protected ExpressionLanguage $expressionLanguage;

    public function __construct(ContainerInterface $container, ExpressionLanguage $expressionLanguage)
    {
        $this->container = $container;
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * Processor configuration
     */
    protected array $configuration;

    public function __invoke(LogRecord $record): LogRecord
    {
        return $record->with(
            context: array_merge($this->evaluateConfiguration(), $record['context'])
        );
    }

    /**
     * Define processor configuration
     *
     * @param array $config
     */
    public function setConfiguration(array $config)
    {
        $this->configuration = $config;
    }

    /**
     * Evaluate configuration array
     */
    protected function evaluateConfiguration(): array
    {
        return array_map(function ($value) {
            return $this->evaluateValue($value);
        }, $this->configuration);
    }

    /**
     * Evaluate configuration value
     */
    protected function evaluateValue(string $value): string
    {
        if (preg_match('/^expr\((.*)\)$/', $value, $matches)) {
            return $this->expressionLanguage->evaluate($matches[1], ['container' => $this->container]);
        }

        return $value;
    }
}
