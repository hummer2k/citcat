<?php
/**
 * CollectHelper
 *
 * @package
 * @author    Cornelius Adams (conlabz GmbH) <ca@conlabz.de>
 */

namespace App\Helper;


use Symfony\Component\Console\Output\OutputInterface;

class CollectHelper
{
    /**
     * @param int $seconds
     * @param OutputInterface $output
     */
    public function wait(int $seconds, OutputInterface $output): void
    {
        for ($i = $seconds; $i > 0; $i--) {
            $output->write(sprintf("Waiting %d seconds...\r", $i));
            sleep(1);
        }
        $output->writeln('');
    }

    /**
     * @param array $friends
     * @param int $queryLimit
     * @return array
     */
    public function generateFromQueries(array $friends, $queryLimit = 400): array
    {
        $queries = [];

        $currentQuery = '';
        $userCount = count($friends);
        foreach ($friends as $i => $user) {
            $currentQuery .= 'from:' . $user->screen_name . ' OR ';
            if (strlen($currentQuery) >= $queryLimit + 4 || $i >= $userCount - 1) {
                $normalizedQuery = substr($currentQuery, 0, strpos($currentQuery, 'from:' . $user->screen_name) - 4);
                $queries[] = $normalizedQuery;
                $currentQuery = '';
            }
        }

        return $queries;
    }

    /**
     * @param array $errors
     * @param OutputInterface $output
     */
    public function outputErrors(array $errors, OutputInterface $output)
    {
        foreach ($errors as $error) {
            $output->writeln(sprintf('<error>%s (Code: %d)</error>', $error->message, $error->code));
        }
    }
}
