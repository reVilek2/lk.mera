<?php
namespace App\Services;

use Jenssegers\Agent\Agent;

class MobileDetectManager
{
    const DESKTOP = 'desktop';
    const MOBILE = 'mobile';
    const TABLET = 'tablet';

    /**
     * @var Agent
     */
    private $agent;

    /**
     * @var string|null
     */
    public $layout_type;
    /**
     * @var string|null
     */
    public $agent_layout_type;

    /**
     * @param Agent $agent
     */
    public function __construct(Agent $agent)
    {
        $this->agent = $agent;
        $this->setLayoutType();
    }

    /**
     * @param null $new_layout_type
     * @return null|string
     */
    public function setLayoutType($new_layout_type = null)
    {
        $agent_layout_type = $this->agent_layout_type;

        if (!$agent_layout_type) {
            $agent_layout_type = $this->updateAgentLayoutType();
        }

        if ($new_layout_type) {
            $this->layout_type = $new_layout_type;
        } else {
            $this->layout_type = $agent_layout_type;
        }

        return $this->layout_type;
    }

    /**
     * @return null|string
     */
    public function updateAgentLayoutType()
    {
        if ($this->agent->isMobile() && !$this->agent->isTablet()) {
            $this->agent_layout_type = self::MOBILE;
        } elseif ($this->agent->isTablet()) {
            $this->agent_layout_type = self::TABLET;
        } else {
            $this->agent_layout_type = self::DESKTOP;
        }

        return $this->agent_layout_type;
    }

    /**
     * @return null|string
     */
    public function getLayoutType()
    {
        return $this->layout_type;
    }

    /**
     * @return bool
     */
    public function is_mobile()
    {
        $layout_type = $this->getLayoutType();
        if ($layout_type === self::MOBILE) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function is_tablet()
    {
        $layout_type = $this->getLayoutType();
        if ($layout_type === self::TABLET) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function is_desktop()
    {
        $layout_type = $this->getLayoutType();
        if ($layout_type === self::MOBILE || $layout_type === self::TABLET) {
            return false;
        }
        return true;
    }
}