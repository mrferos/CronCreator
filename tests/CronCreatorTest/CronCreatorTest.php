<?php
namespace CronCreatorTest;

use CronCreator\CronCreator;
use org\bovigo\vfs\vfsStream;

class CronCreatorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        vfsStream::setup('writer_test_dir');
    }

    public function testGetInstanceIsCronCreatorInstance()
    {
        $cronCreator = CronCreator::getInstance();
        $this->assertInstanceOf('CronCreator\CronCreator', $cronCreator);
    }

    /**
     * @depends testGetInstanceIsCronCreatorInstance
     */
    public function testGetInstanceHasDefaultCreator()
    {
        $cronCreator = CronCreator::getInstance();
        $reflObject = new \ReflectionObject($cronCreator);
        $reflProp = $reflObject->getProperty('_creator');
        $reflProp->setAccessible(true);
        $this->assertInstanceOf('CronCreator\Creator', $reflProp->getValue($cronCreator));
    }

    /**
     * @depends testGetInstanceIsCronCreatorInstance
     */
    public function testGetInstanceHasDefaultWriter()
    {
        $cronCreator = CronCreator::getInstance();
        $reflObject = new \ReflectionObject($cronCreator);
        $reflProp = $reflObject->getProperty('_writer');
        $reflProp->setAccessible(true);
        $this->assertInstanceOf('CronCreator\Writer', $reflProp->getValue($cronCreator));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCronCreatorAddMethodNotAcceptUnsupportedType()
    {
        $cronCreator = new CronCreator(
            $this->getMock('\CronCreator\Creator'),
            $this->getMock('\CronCreator\Writer')
        );

        $cronCreator->add(new \stdClass());
    }

    public function testCronCreatorAddMethodAcceptCreatorInterface()
    {
        $cronCreator = new CronCreator(
            $this->getMock('\CronCreator\Creator'),
            $this->getMock('\CronCreator\Writer')
        );

        $creatorInterfaceMock = $this->getMock('\CronCreator\CreatorInterface');
        $creatorInterfaceMock->expects($this->once())
                                ->method('getLine')
                                ->will($this->returnValue('verify'));

        $cronCreator->add($creatorInterfaceMock);
        $reflObj = new \ReflectionObject($cronCreator);
        $reflProp = $reflObj->getProperty('_cronJobs');
        $reflProp->setAccessible(true);

        $cronJobs = $reflProp->getValue($cronCreator);
        $this->assertInternalType('array', $cronJobs);
        $this->assertEquals('verify', $cronJobs[0]);
    }

    public function testCronCreatorAddMethodAcceptsArrayAndRoutesToEvery()
    {
        $creatorMock = $this->getMock('\CronCreator\Creator');
        $creatorMock->expects($this->once())
                        ->method('clear');

        $creatorMock->expects($this->once())
                        ->method('every')
                        ->with(2, 'minute');

        $cronCreator = new CronCreator(
            $creatorMock,
            $this->getMock('\CronCreator\Writer')
        );

        $cronCreator->add(array(
            'every' => array(
                'amount' => 2,
                'unit'   => 'minute'
            )
        ));
    }

    public function testCronCreatorAddMethodAcceptsArrayAndDefaultsCorrectly()
    {
        $creatorMock = $this->getMock('\CronCreator\Creator');
        $creatorMock->expects($this->once())
            ->method('clear');

        $creatorMock->expects($this->once())
            ->method('at')
            ->with(array('foo' => 'rawr'));

        $cronCreator = new CronCreator(
            $creatorMock,
            $this->getMock('\CronCreator\Writer')
        );

        $cronCreator->add(array(
            'at' => array('foo' => 'rawr')
        ));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testCronCreatorAddMethodExceptionsOnInvalidArrayOption()
    {
        $cronCreator = new CronCreator(
            $this->getMock('\CronCreator\Creator'),
            $this->getMock('\CronCreator\Writer')
        );

        $cronCreator->add(array('foo' => 'rawr'));
    }

    public function testGetWriter()
    {
        $writerMock = $this->getMock('\CronCreator\Writer');
        $cronCreator = new CronCreator(
            $this->getMock('\CronCreator\Creator'),
            $writerMock
        );

        $this->assertEquals(spl_object_hash($writerMock), spl_object_hash($cronCreator->getWriter()));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testWriteWithEmptyCronJobsResultsInException()
    {
        $cronCreator = new CronCreator(
            $this->getMock('\CronCreator\Creator'),
            $this->getMock('\CronCreator\Writer')
        );

        $cronCreator->save();
    }

    public function testWriteWithEmptyFileProducesDefaultFile()
    {
        $cronJobFile = vfsStream::url('writer_test_dir/test_cron_file');
        $writerMock = $this->getMock('\CronCreator\Writer');
        $writerMock->expects($this->once())
                    ->method('setCronjobFile')
                    ->with($cronJobFile);

        $cronCreator = new CronCreator(
            $this->getMock('\CronCreator\Creator'),
            $writerMock
        );


        $reflObject = new \ReflectionObject($cronCreator);
        $defaultCronFileProp = $reflObject->getProperty('_defaultCronJobFile');
        $defaultCronFileProp->setAccessible(true);
        $defaultCronFileProp->setValue($cronCreator, $cronJobFile);

        $cronCreator->add(array(
            'every' => array(
                'amount' => 2,
                'unit'   => 'minute'
            )
        ));

        $cronCreator->save();
    }
}