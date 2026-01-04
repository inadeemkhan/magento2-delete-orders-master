<?php
namespace DevScripts\DeleteOrders\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Delete extends Action
{
    protected $orderCollectionFactory;

    public function __construct(
        Context $context,
        CollectionFactory $orderCollectionFactory
    ) {
        parent::__construct($context);
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('DevScripts_DeleteOrders::config');
    }

    public function execute()
    {
        $ids = $this->getRequest()->getParam('selected', []);
        $deleted = 0;

        if (!empty($ids)) {
            $collection = $this->orderCollectionFactory->create()
                ->addFieldToFilter('entity_id', ['in' => $ids]);

            foreach ($collection as $order) {
                try {
                    $order->delete();
                    $deleted++;
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Error deleting order ID %1', $order->getId()));
                }
            }
        }

        if ($deleted) {
            $this->messageManager->addSuccessMessage(__('%1 order(s) deleted successfully.', $deleted));
        } else {
            $this->messageManager->addNoticeMessage(__('No orders were deleted.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/order/index');
    }
}