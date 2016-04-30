<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\Log\Formatter;

use Monolog\Formatter\JsonFormatter as MonologJsonFormatter;

/**
 * Encodes whatever record data is passed to it as JSON. This JSON formatter is different to
 * the default Monolog JSON formatter, as it checks the record's context for an 'exception'.
 * If the context contains an exception object, it's data is parsed and appended to the record
 * data. This also means, that the original message is overwritten with the exception message.
 *
 * @category  Shopware
 * @package   Shopware\Components\Log\Formatter
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class JsonFormatter extends MonologJsonFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $recordData = [
            'level' => $record['level_name'],
            'timestamp' => $record['datetime']->format(\DateTime::ISO8601),
            'message' => $record['message'],
            'context' => $record['context'] ?: []
        ];

        if (isset($record['extra']) && is_array($record['extra']) && isset($record['extra']['uid'])) {
            $recordData['uid'] = $record['extra']['uid'];
        }

        if (isset($recordData['context']['exception']) && $recordData['context']['exception'] instanceof \Exception) {
            // Use the exception to update the record data
            $exception = $recordData['context']['exception'];
            unset($recordData['context']['exception']);
            $recordData['message'] = $exception->getMessage();
            $recordData['code'] = $exception->getCode();
            $recordData['file'] = $exception->getFile();
            $recordData['line'] = $exception->getLine();
            $recordData['trace'] = $exception->getTrace();
        }

        return json_encode($recordData) . ($this->appendNewline ? "\n" : '');
    }

    /**
     * {@inheritdoc}
     */
    protected function formatBatchJson(array $records)
    {
        $instance = $this;

        return json_encode(array_map(function($record) use ($instance) {
            return $this->format($record);
        }, $records));
    }
}
