<?php
namespace CronCreator;

interface WriterInterface
{
    /**
     * @return string
     */
    public function getCronjobFile();

    /**
     * @param string $cronjobFile
     */
    public function setCronjobFile($cronjobFile);

    /**
     * @return string
     */
    public function getMode();

    /**
     * @param string $mode
     */
    public function setMode($mode);

    /**
     * Given an array of cronjob lines, write them to the specified
     * cronjob line. Possibilities given the modes:
     *
     * * insert: will append to the end of the file
     * * replace: will replace the contents of the file with the specifies cronlines
     * * upsert: will see if any of the specified cronjobs are already in the file and does not set them.
     * Only new cronjobs are set.
     *
     * @param array $cronjobs
     */
    public function write(array $cronjobs);

    /**
     * Read the cronjob file and return the lines
     *
     * @return array
     */
    public function read();
}