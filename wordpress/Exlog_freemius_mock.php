<?php
class Exlog_freemius_mock
{
    protected $can_use_premium_code = true;
    protected $plan = 'free';

    public function toggle_premium_code()
    {
        $this->can_use_premium_code = !$this->can_use_premium_code;
    }
    public function can_use_premium_code()
    {
        return $this->can_use_premium_code;
    }
    public function can_use_premium_code__premium_only()
    {
        return $this->can_use_premium_code;
    }
    public function is_plan($plan, $exact=false)
    {
        $plans = ['free','pro'];
        $id_cur = array_search($this->plan, $plans);
        $id_req = array_search($plan, $plans);
        return ($exact) ? ($id_cur == $id_req) : ($id_cur >= $id_req);
    }
    public function is_plan_or_trial($plan, $exact=false)
    {
        return $this->is_plan($plan, $exact);
    }
    public function is_plan__premium_only($plan, $exact=false)
    {
        return $this->is_plan($plan, $exact);
    }
    public function is_plan_or_trial__premium_only($plan, $exact=false)
    {
        return $this->is_plan($plan, $exact);
    }
    public function is__premium_only()
    {
        return true;
    }
    public function add_action($a, $b) { }
    public function add_filter($a, $b) { }
    public function set_basename($is_premium, $caller) { }
    public function set_plan($plan)
    {
        $this->plan = $plan;
    }
}

global $exlog_freemius;
$exlog_freemius = new Exlog_freemius_mock();
function wf_fs()
{
    global $exlog_freemius;
    return $exlog_freemius;
}

if (isset($_COOKIE[EXLOG_TEST_PLAN_COOKIE])) {
    exlog_freemius()->set_plan($_COOKIE[EXLOG_TEST_PLAN_COOKIE]);
}
