<?php

declare(strict_types=1);

namespace Brain\Console;

use Brain\Facades\Terminal;
use Exception;
use Illuminate\Console\OutputStyle;

class Printer
{
    /**
     * Colors that represent the different elements.
     */
    private array $elemColors = [
        'DOMAIN' => '#6C7280',
        'PROC' => 'blue',
        'TASK' => 'yellow',
        'QERY' => 'green',
        'TESTED' => 'green',
        'NOTEST' => 'red',
    ];

    /**
     * @var int The width of the terminal in characters.
     */
    private int $terminalWidth;

    /**
     * @var int The length of the longest domain in the brain's map.
     */
    private int $lengthLongestDomain = 0;

    /**
     * @var array The lines to be printed to the terminal.
     */
    private array $lines = [];

    public function __construct(
        private readonly BrainMap $brain,
        private ?OutputStyle $output = null
    ) {
        $this->checkIfBrainMapIsEmpty();
        $this->getTerminalWidth();
        $this->getLengthOfTheLongestDomain();
        $this->createLines();
    }

    /**
     * Prints the collected lines to the output.
     *
     * This method uses the `collect` helper to create a collection
     * from the `$lines` property, flattens the collection, and writes
     * the resulting output using the `$output`'s `writeln` method.
     */
    public function print(): void
    {
        $flattenedLines = [];
        array_walk_recursive($this->lines, function ($line) use (&$flattenedLines): void {
            $flattenedLines[] = $line;
        });

        $this->output->writeln($flattenedLines);

        $this->printTests();
    }

    /**
     * Sets the output style for the printer.
     *
     * @param  OutputStyle  $output  The output style instance to be set.
     */
    public function setOutput(OutputStyle $output): void
    {
        $this->output = $output;
    }

    /**
     * Prints the test coverage information to the output.
     *
     * This method calculates the number of tests and the maximum
     * test count, then formats and prints the coverage information
     * to the output. It also checks if the coverage percentage is
     * below a specified minimum threshold and prints a warning if so.
     */
    private function printTests(): void
    {
        $tests = collect($this->brain->tested)
            ->filter(fn ($value): bool => $value)
            ->count();
        $maxTestCount = count($this->brain->tested);

        $this->output->writeln('');
        $this->output->writeln(sprintf(
            '  <fg=white;options=bold>TESTS:  </><fg=green;options=bold>%d/%d</>',
            $tests,
            $maxTestCount
        ));

        $minimumCoverage = (float) config('brain.test_minimum_coverage', 0.0);
        if ($minimumCoverage > 0) {
            $coveragePercentage = ($tests / $maxTestCount) * 100.0;

            if ($coveragePercentage < $minimumCoverage) {
                $this->output->writeln(
                    sprintf(
                        '  <bg=red;options=bold> FAIL </>  Test coverage below expected: <fg=red;options=bold>%.1f%%</>. Minimum: <fg=white;options=bold>%.1f%%</>',
                        $coveragePercentage,
                        $minimumCoverage
                    )
                );
            } else {
                $this->output->writeln(sprintf(
                    '  <bg=green;options=bold> PASS </>  <fg=white;options=bold>Coverage</> <fg=green;options=bold>%.1f%%</>. <fg=white;options=bold>Minimum</> <fg=white;options=bold>%.1f%%</>',
                    $coveragePercentage,
                    $minimumCoverage

                ));
            }
        }
    }

    /**
     * Checks if the brain map is empty.
     *
     * This method verifies whether the brain map is empty or not.
     * If the brain map is empty, it throws an exception to indicate
     * that the operation cannot proceed with an empty brain map.
     *
     * @throws Exception If the brain map is empty.
     */
    private function checkIfBrainMapIsEmpty(): void
    {
        if (! $this->brain->map instanceof \Illuminate\Support\Collection || $this->brain->map->isEmpty()) {
            throw new Exception('The brain map is empty.');
        }
    }

    /**
     * Creates lines for the console output by iterating over the brain's map data.
     *
     * This method processes each domain data entry and generates formatted lines
     * for processes, tasks, and queries. It also adds a new line after processing
     * each domain data entry.
     *
     * Example Output:
     * | 1    | 2    | 3      | 4                 | 5                   | 6   |
     * |------|------|--------|-------------------|---------------------|-----|
     * | USER | PROC | NOTEST | CreateUserProcess | ....................|     |
     * |      | TASK | TESTED | CreateUser        | ....................|     |
     * |      | TASK | TESTED | WelcomeNofication | ............. queued|     |
     * |      | QERY | NOTEST | SomeQuery         | ....................|     |
     *
     * - `1`: Domain spaces (e.g., USER)
     * - `2`: Type (e.g., PROC, TASK, QERY)
     * - `3`: Test (e.g., TESTED, NOTEST)
     * - `4`: Class name or identifier
     * - `5`: Mixed properties (e.g., chained, queued)
     */
    private function createLines(): void
    {
        $this->brain->map->each(function ($domainData): void {
            $domain = data_get($domainData, 'domain');
            $domainSpaces = $this->getDomainSpaces($domain);

            $this->addProcessesLine($domainData, $domain, $domainSpaces);
            $this->addTasksLine($domainData, $domain, $domainSpaces);
            $this->addQueriesLines($domainData, $domain, $domainSpaces);

            $this->addNewLine();
        });
    }

    /**
     * Adds a formatted line for each process in the given domain data to the output lines.
     *
     * This method iterates over the processes in the provided domain data and constructs
     * a formatted string for each process. The string includes the domain name, process name,
     * and additional metadata such as whether the process is part of a chain. The formatted
     * string is styled with terminal colors and added to the `$lines` property.
     *
     * @param  array  $domainData  An associative array containing domain data, including a 'processes' key.
     *                             Each process should have a 'name' key and a 'chain' key.
     * @param  string  $currentDomain  The name of the current domain being processed.
     * @param  string  $spaces  A string of spaces used for alignment in the output.
     */
    private function addProcessesLine(array $domainData, string $currentDomain, string $spaces): void
    {
        foreach (data_get($domainData, 'processes') as $process) {
            $processName = data_get($process, 'name');
            $inChain = $process['chain'] ? ' chained' : '.';
            $processHasTest = $process['has_test']
                ? ' <fg='.$this->elemColors['TESTED'].'>TESTED</>  '
                : ' <fg='.$this->elemColors['NOTEST'].'>NOTEST</>  ';
            $dots = str_repeat('.', max($this->terminalWidth - mb_strlen($currentDomain.$processName.$spaces.$inChain.'PROC  ') - mb_strlen(strip_tags($processHasTest)) - 3, 0));
            $dots = $dots === '' || $dots === '0' ? $dots : " $dots";

            $this->lines[] = [
                sprintf(
                    '  <fg=%s;options=bold>%s</>%s<fg=%s;options=bold>%s</> %s<fg=%s>%s</><fg=#6C7280>%s%s</>',
                    $this->elemColors['DOMAIN'],
                    strtoupper($currentDomain),
                    $spaces,
                    $this->elemColors['PROC'],
                    'PROC',
                    $processHasTest,
                    'white',
                    $processName,
                    $dots,
                    $inChain
                ),
            ];
        }
    }

    /**
     * Adds a formatted line for each task in the given domain data to the output lines.
     *
     * @param  array  $domainData  The data for the current domain, containing tasks and their details.
     * @param  string  $currentDomain  The name of the current domain being processed.
     * @param  string  $spaces  The string of spaces used for indentation.
     * @param  bool  $numberedIndex  Whether to prefix tasks with a numbered index (default: false).
     */
    private function addTasksLine(array $domainData, string $currentDomain, string $spaces, bool $numberedIndex = false): void
    {
        foreach (data_get($domainData, 'tasks') as $taskIndex => $task) {
            $taskIndex++;
            $prefix = $numberedIndex ? "{$taskIndex}. " : '';
            $taskName = $task['name'];
            $taskSpaces = str_repeat(' ', 2 + mb_strlen($currentDomain) + mb_strlen($spaces));
            $taskQueued = $task['queue'] ? ' queued' : '.';
            $taskHasTest = $task['has_test']
                ? ' <fg='.$this->elemColors['TESTED'].'>TESTED</>  '
                : ' <fg='.$this->elemColors['NOTEST'].'>NOTEST</>  ';
            $taskDots = str_repeat('.', max($this->terminalWidth - mb_strlen($taskSpaces.$taskIndex.$taskName.'TASK  ') - mb_strlen($taskQueued) - mb_strlen(strip_tags($taskHasTest)), 0));
            $taskDots = $taskDots === '' || $taskDots === '0' ? $taskDots : " $taskDots";

            $this->lines[] = [
                sprintf(
                    '%s<fg=%s;options=bold>%s</> %s<fg=white>%s%s</><fg=#6C7280>%s%s</>',
                    $taskSpaces,
                    $this->elemColors['TASK'],
                    'TASK',
                    $taskHasTest,
                    $prefix,
                    $taskName,
                    $taskDots,
                    $taskQueued
                ),
            ];
        }
    }

    /**
     * Adds formatted query lines to the console output.
     *
     * This method processes the queries from the provided domain data and formats
     * them for display in the console. Each query is displayed with a specific
     * structure, including spaces, dots, and color formatting.
     *
     * @param  array  $domainData  The data containing domain-specific information,
     *                             including a list of queries.
     * @param  string  $currentDomain  The name of the current domain being processed.
     * @param  string  $spaces  The base indentation spaces for formatting.
     */
    private function addQueriesLines(array $domainData, string $currentDomain, string $spaces): void
    {
        foreach (data_get($domainData, 'queries') as $query) {
            $queryName = $query['name'];
            $queryHasTest = $query['has_test']
                ? ' <fg='.$this->elemColors['TESTED'].'>TESTED</>  '
                : ' <fg='.$this->elemColors['NOTEST'].'>NOTEST</>  ';
            $querySpaces = str_repeat(' ', 2 + mb_strlen($currentDomain) + mb_strlen($spaces));
            $queryDots = str_repeat('.', max($this->terminalWidth - mb_strlen($querySpaces.$queryName.'QERY ') - mb_strlen(strip_tags($queryHasTest)) - 2, 0));

            $this->lines[] = [
                sprintf(
                    '%s<fg=%s;options=bold>%s</> %s<fg=white>%s</><fg=#6C7280> %s</>',
                    $querySpaces,
                    $this->elemColors['QERY'],
                    'QERY',
                    $queryHasTest,
                    $queryName,
                    $queryDots
                ),
            ];
        }
    }

    /**
     * Generates a string of spaces to align domain names for console output.
     *
     * This method calculates the number of spaces needed to align a given domain
     * name based on the length of the longest domain name and returns a string
     * containing that many spaces.
     *
     * @param  string  $domain  The domain name for which to calculate the spacing.
     * @return string A string of spaces for alignment.
     */
    private function getDomainSpaces(string $domain): string
    {
        return str_repeat(' ', max($this->lengthLongestDomain + 2 - mb_strlen($domain), 0));
    }

    /**
     * Adds a new empty line to the lines array if the last line is not already empty.
     * This ensures that there is a separation or spacing between lines when needed.
     */
    private function addNewLine(): void
    {
        if (end($this->lines) === ['']) {
            return;
        }

        $this->lines[] = [''];
    }

    /**
     * Calculates the length of the longest domain in the brain's map.
     *
     * This method iterates through the brain's map, sorts the entries
     * in descending order based on the length of the 'domain' value,
     * and retrieves the length of the longest domain.
     */
    private function getLengthOfTheLongestDomain(): void
    {
        $this->lengthLongestDomain = mb_strlen(
            (string) data_get($this->brain->map
                ->sortByDesc(fn ($value): int => mb_strlen((string) $value['domain']))
                ->first(), 'domain')
        );
    }

    /**
     * Retrieves and sets the terminal's current width by using the Terminal utility.
     *
     * This method assigns the number of columns (width) of the terminal
     * to the `$terminalWidth` property.
     */
    private function getTerminalWidth(): void
    {
        $this->terminalWidth = Terminal::cols();
    }
}
