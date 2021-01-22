<?php
/**
 * This expects vcl configuration like:
 * if (req.url.qs ~ "overlay") {
 *     set req.http.X-fastly-imageopto-overlay = "overlay=/media/catalog/product/watermark/"+ subfield(req.url.qs, "overlay", "&") +"&overlay-height=0.80&overlay-width=0.80";
 * }
 *
 * @category Fastly
 * @package  Andrewdaluz_Fastly
 **/
namespace Andrewdaluz\Fastly\Plugin\Model\View\Asset;

use Magento\PageCache\Model\Config as PageCacheConfig;
use Fastly\Cdn\Model\Config;

/**
 * Class Image
 * 
 * @package Andrewdaluz\Fastly\Plugin\Model\View\Asset
 */
class Image
{
    /**
     * @var bool
     */
    protected $isFastlyEnabled = null;

    /**
     * @var null
     */
    protected $isForceLossyEnabled = null;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string|null
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function afterGetUrl(\Fastly\Cdn\Model\View\Asset\Image $subject, $result)
    {
        if ($this->isFastlyImageOptimizationEnabled() == false && $this->isForceLossyEnabled() == false) {
            return $result;
        }

        if ($this->isWatermarkEnabled() && $this->getWatermarkImage()) {
            $result .= '&overlay='.$this->getWatermarkImage();
        }
        
        return $result;
    }

    /**
     * @return boolean
     */
    public function isWatermarkEnabled()
    {
        return $this->scopeConfig->isSetFlag('andrewdaluz_fastly/watermark/enable');
    }

    /**
     * @return string|null
     */
    public function getWatermarkImage()
    {
        return $this->scopeConfig->getValue('design/watermark/image_image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool|null
     */
    public function isForceLossyEnabled()
    {
        if ($this->isForceLossyEnabled !== null) {
            return $this->isForceLossyEnabled;
        }

        $this->isForceLossyEnabled = true;

        if (empty($this->scopeConfig->isSetFlag(Config::XML_FASTLY_FORCE_LOSSY))) {
            $this->isForceLossyEnabled = false;
        }

        if ($this->scopeConfig->getValue(PageCacheConfig::XML_PAGECACHE_TYPE) !== Config::FASTLY) {
            $this->isForceLossyEnabled = false;
        }

        return $this->isForceLossyEnabled;
    }

    /**
     * On/Off switch based on config value
     *
     * @return bool
     */
    protected function isFastlyImageOptimizationEnabled()
    {
        if ($this->isFastlyEnabled !== null) {
            return $this->isFastlyEnabled;
        }

        $this->isFastlyEnabled = true;

        if ($this->scopeConfig->isSetFlag(Config::XML_FASTLY_IMAGE_OPTIMIZATIONS) == false) {
            $this->isFastlyEnabled = false;
        }

        if ($this->scopeConfig->getValue(PageCacheConfig::XML_PAGECACHE_TYPE) !== Config::FASTLY) {
            $this->isFastlyEnabled = false;
        }

        return $this->isFastlyEnabled;
    }
}
