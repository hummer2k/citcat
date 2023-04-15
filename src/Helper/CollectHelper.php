<?php

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
        foreach ($friends as $i => $currentUser) {
            $currentQuery .= 'from:' . $currentUser->screen_name . ' OR ';
            $length = strlen($currentQuery);
            $nextUser = $friends[$i + 1] ?? false;

            if ($nextUser) {
                $length += strlen('from:' . $nextUser->screen_name);
            }

            if ($length > $queryLimit || $i >= $userCount - 1) {
                $queries[] = substr($currentQuery, 0, -4);
                $currentQuery = '';
            }
        }

        return $queries;
    }

    /**
     * @param $bytes
     * @param int $precision
     * @return string
     */
    public function formatBytes($bytes, $precision = 2) : string
    {
        $unit = ['B', 'KB', 'MB', 'GB'];
        $exp = floor(log($bytes, 1024)) | 0;
        return round($bytes / (pow(1024, $exp)), $precision) . $unit[$exp];
    }

    /**
     * @param $tweetData
     * @return array
     */
    public function normalizeTweetData($tweetData): array
    {
        return \json_decode(\json_encode($tweetData), true);
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
