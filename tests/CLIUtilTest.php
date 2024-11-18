<?php

use K92\Phputils\CLIUtil;
use PHPUnit\Framework\TestCase;

class CLIUtilTest extends TestCase
{
    public function test_kill_process_tree(): void
    {
        $pid = $this->helper_fork();

        $this->assertTrue((bool) $pid);

        $output = exec("ps aux | grep $pid | grep -v grep");

        $this->assertFalse(str_contains($output, '[php] <defunct>'));

        $this->assertTrue(CLIUtil::killProcessTree($pid));

        $output = exec("ps aux | grep $pid | grep -v grep");

        $this->assertTrue(str_contains($output, '[php] <defunct>'));
    }

    protected function helper_fork(): int|false
    {
        $pid = pcntl_fork();

        if ($pid === -1) {
            return false;
        } elseif ((bool) $pid) {
            // in parent
            return $pid;
        } else {
            // find free port
            $sock = socket_create_listen(0);
            socket_getsockname($sock, $addr, $port);
            socket_close($sock);

            // in child
            exec("php -S localhost:$port");

            exit(0);
        }
    }
}
