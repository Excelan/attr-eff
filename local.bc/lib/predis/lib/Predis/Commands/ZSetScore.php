<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Predis\Commands;

/**
 * @link http://redis.io/commands/zscore
 * @author Daniele Alessandri <suppakilla@gmail.com>
 */
class ZSetScore extends PrefixableCommand
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'ZSCORE';
    }
}