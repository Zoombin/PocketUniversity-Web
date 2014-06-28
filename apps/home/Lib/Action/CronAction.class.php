<?php
class CronAction extends Action{

    public function cron() {
        Addons::hook("cron_sync_weibo");
    }
}