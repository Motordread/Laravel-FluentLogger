<?php

/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 *
 * Copyright (c) 2015-2016 Yuuki Takezawa
 *
 */
namespace Ytake\LaravelFluent;

use Fluent\Logger\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Psr\Log\LogLevel;

/**
 * Class FluentHandler
 */
class FluentHandler extends AbstractProcessingHandler
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $tagFormat = '{{channel}}.{{level_name}}';

    /**
     * FluentHandler constructor.
     *
     * @param LoggerInterface $logger
     * @param null|string     $tagFormat
     * @param int             $level
     * @param bool            $bubble
     */
    public function __construct(LoggerInterface $logger, $tagFormat = null, $level = Logger::DEBUG, $bubble = true)
    {
        $this->logger = $logger;
        $this->tagFormat = $tagFormat;
        parent::__construct($level, $bubble);
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        $payload = [
            'message' => $record['message'],
            'context' => $record['context'],
            'extra' => $record['extra'],
        ];

        if ($this->tagFormat != 'google')
            $tag = $this->populateTag($record);
        else {
            //compatibility with google error-reporting

            $errors = array(
                LogLevel::NOTICE => "Notice",
                LogLevel::WARNING => "Warning",
                LogLevel::ERROR => "Fatal error"
            );

            if (in_array($this->getLowerCaseLevelName($record), array_keys($errors))) {
                $recognized_error_type = $errors[strtolower($record['level_name'])];
                $payload['message'] = 'PHP ' . $recognized_error_type . ' ' . $record['message'];
                $tag = 'errors';
            } else {
                $tag = $record['level_name'];
            }

            $payload['severity'] = $record['level_name'];
        }

        $this->logger->post(
            $tag,
            $payload
        );
    }

    /**
     * @param array $record
     *
     * @return string
     */
    protected function populateTag(array $record)
    {
        return $this->processFormat($record, $this->tagFormat);
    }

    /**
     * @param array  $record
     * @param string $tag
     *
     * @return string
     * @throws \Exception
     */
    protected function processFormat(array $record, $tag)
    {
        if (preg_match_all('/\{\{(.*?)\}\}/', $tag, $matches)) {
            foreach ($matches[1] as $match) {
                if (!isset($record[$match])) {
                    throw new \LogicException('No such field in the record');
                }
                $tag = str_replace(sprintf('{{%s}}', $match), $record[$match], $tag);
            }
        }

        return $tag;
    }

    public function getLowerCaseLevelName($record)
    {
        return strtolower($record['level_name']);
    }
}
