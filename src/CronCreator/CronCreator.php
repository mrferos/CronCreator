<?php
namespace CronCreator;

class CronCreator
{
    /**
     * @var Creator
     */
    protected $_creator;
    /**
     * @var Writer
     */
    protected $_writer;
    /**
     * Holds array of cronjobs to add
     *
     * @var array
     */
    protected $_cronJobs = array();
    /**
     * Holds the default cronjob file
     *
     * @var string
     */
    protected $_defaultCronJobFile;

    /**
     * @return CronCreator
     */
    public static function getInstance()
    {
        return new static(new Creator(), new Writer());
    }

    public function __construct(Creator $creator, Writer $writer)
    {
        $this->_defaultCronJobFile = '/etc/cron.d/easy-crons';
        $this->_creator = $creator;
        $this->_writer = $writer;
    }

    /**
     * Add a cronjob using an array
     *
     * @param array|CreatorInterface $cronJobSettings
     */
    public function add($cronJobSettings)
    {
        if ($cronJobSettings instanceof CreatorInterface) {
            $creator = $cronJobSettings;
        } elseif (is_array($cronJobSettings)) {
            $creator = $this->_creator;
            $creator->clear();
            foreach ($cronJobSettings as $key => $value) {
                switch ($key) {
                    case 'every':
                        $creator->every($value['amount'], $value['unit']);
                        break;
                    default:
                        if (!method_exists($creator, $key)) {
                            throw new \RuntimeException('Unknown method ' . $key);
                        }

                        call_user_func(array($creator, $key), $value);
                }
            };

        } else {
            throw new \InvalidArgumentException('Only array and CreatorInterface are supported');
        }

        $this->_cronJobs[] = $creator->getLine();
    }

    public function save($file = null, $mode = Writer::MODE_UPSERT)
    {
        if (empty($this->_cronJobs)) {
            throw new \RuntimeException('No cronjobs to write');
        }

        $file = is_null($file) ? $this->_getDefaultCronFile() : $file;
        $this->_writer->setCronjobFile($file);
        $this->_writer->setMode($mode);
        $this->_writer->write($this->_cronJobs);
    }

    /**
     * @return Writer
     */
    public function getWriter()
    {
        return $this->_writer;
    }

    /**
     * Returns default cron file, if it does not exists, attempt to create it.
     *
     * @return string
     */
    protected function _getDefaultCronFile()
    {
        $file = $this->_defaultCronJobFile;
        if (!file_exists($file)) {
            if (!is_writable(dirname($file))) {
                throw new \RuntimeException("'{$file}' can not be created");
            }

            touch($file);
            if (!file_exists($file)) {
                throw new \RuntimeException('Could not create "' . $file . '"');
            }

        }

        return $file;
    }
}