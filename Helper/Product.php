<?php

namespace MageSuite\ProductPositiveIndicators\Helper;

class Product extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \MageSuite\ProductPositiveIndicators\Service\FreeShippingInterface
     */
    protected $freeShippingService;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageSuite\ProductPositiveIndicators\Helper\Configuration\PopularIcon
     */
    protected $popularIconConfiguration;

    /**
     * @var \MageSuite\ProductPositiveIndicators\Helper\Configuration\FastShipping
     */
    protected $fastShippingConfiguration;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \MageSuite\ProductPositiveIndicators\Service\FreeShippingInterface $freeShippingService,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \MageSuite\ProductPositiveIndicators\Helper\Configuration\PopularIcon $popularIconConfiguration,
        \MageSuite\ProductPositiveIndicators\Helper\Configuration\FastShipping $fastShippingConfiguration
    ) {
        parent::__construct($context);

        $this->productResource = $productResource;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfigInterface;
        $this->freeShippingService = $freeShippingService;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->fastShippingConfiguration = $fastShippingConfiguration;
        $this->popularIconConfiguration = $popularIconConfiguration;
    }

    public function getPopularIconFlag($product)
    {
        if(!$this->popularIconConfiguration->isEnabled()) {
            return false;
        }

        $product = $this->getProduct($product);

        if(!$product){
            return false;
        }

        $isPopularIconEnabled = $this->isPopularIconEnabled($product);

        if(!$isPopularIconEnabled){
            return $isPopularIconEnabled;
        }

        $currentProduct = $this->registry->registry('product');
        $currentCategory = $this->registry->registry('current_category');

        if(!$currentCategory or $currentProduct){
            return $isPopularIconEnabled;
        }

        $enabledInCategory = $this->isEnabledInSpecificCategory($product, $currentCategory->getId());

        return $enabledInCategory;
    }

    public function getProduct($product = null)
    {
        if(!$product){
            $product = $this->registry->registry('product');
            return $product ? $product : null;
        }

        if ($product instanceof \Magento\Catalog\Api\Data\ProductInterface) {
            return $product;
        }

        if (!is_int($product) and !is_string($product)) {
            return null;
        }

        try {
            $product = $this->productRepository->getById($product);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }

        return $product;
    }

    private function isPopularIconEnabled($product)
    {
        return (boolean)$product->getPopularIcon();
    }

    private function isEnabledInSpecificCategory($product, $categoryId)
    {
        $enabledInCategories = $product->getPopularIconCategories();

        if(!$enabledInCategories){
            return false;
        }

        $categories = explode(',', $enabledInCategories);

        return in_array($categoryId, $categories) ? true : false;
    }

    public function isFastShippingEnabled()
    {
        if(!$this->fastShippingConfiguration->isEnabled()){
            return false;
        }

        return true;
    }

    public function isFreeShipped($product)
    {
        return $this->freeShippingService->isFreeShipped($product);
    }

    public function showFreeShippingInProductTiles(){
        return $this->freeShippingService->showInProductTiles();
    }

    public function showFreeShippingTextNoteOnProductsDetailpage(){
        return $this->freeShippingService->showTextNoteOnProductsDetailpage();
    }

    public function showFreeShippingBadgeOnProductsDetailpage(){
        return $this->freeShippingService->showBadgeOnProductsDetailpage();
    }

    public function showFreeShippingInSearchAutosuggest(){
        return $this->freeShippingService->showInSearchAutosuggest();
    }


}
