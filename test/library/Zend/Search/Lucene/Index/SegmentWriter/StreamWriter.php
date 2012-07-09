<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace Zend\Search\Lucene\Index\SegmentWriter;
use Zend\Search\Lucene\Storage\Directory;
use Zend\Search\Lucene\Index as LuceneIndex;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 */
class StreamWriter extends AbstractSegmentWriter
{
    /**
     * Object constructor.
     *
     * @param Directory\DirectoryInterface $directory
     * @param string $name
     */
    public function __construct(Directory\DirectoryInterface $directory, $name)
    {
        parent::__construct($directory, $name);
    }


    /**
     * Create stored fields files and open them for write
     */
    public function createStoredFieldsFiles()
    {
        $this->_fdxFile = $this->_directory->createFile($this->_name . '.fdx');
        $this->_fdtFile = $this->_directory->createFile($this->_name . '.fdt');

        $this->_files[] = $this->_name . '.fdx';
        $this->_files[] = $this->_name . '.fdt';
    }

    public function addNorm($fieldName, $normVector)
    {
        if (isset($this->_norms[$fieldName])) {
            $this->_norms[$fieldName] .= $normVector;
        } else {
            $this->_norms[$fieldName] = $normVector;
        }
    }

    /**
     * Close segment, write it to disk and return segment info
     *
     * @return \Zend\Search\Lucene\Index\SegmentInfo
     */
    public function close()
    {
        if ($this->_docCount == 0) {
            return null;
        }

        $this->_dumpFNM();
        $this->_generateCFS();

        return new LuceneIndex\SegmentInfo($this->_directory,
                                           $this->_name,
                                           $this->_docCount,
                                           -1,
                                           null,
                                           true,
                                           true);
    }
}

