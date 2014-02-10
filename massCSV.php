<?php
/////////////////////////////////////////////////////////////////////////
//Author: Chris Anderson
//Description: Import all codes in a csv file and put them into magento.//
//
/////////////////////////////////////////////////////////////////////////
 
require_once('../app/Mage.php'); // this will only allow one instance of the mage class to be compliled into the following
                                 // this way we (computer scientist can ensure there is no rreference errors or spoofing                              
Mage::app(); // instanciate Mage Interface 
 
// assign the file object to the $handle variable, which points to the firs memory address.
$handle = fopen('mannaMunnCoupon.csv', 'r');
$cols   = array_flip(fgetcsv($handle));
 
//where the magic happens
//loop through file stream while stream pointer 
//'$data' does not 
while($data = fgetcsv($handle))
{
  if($data[$cols['is_active']] == 1)
    {
        echo 'Importing coupon with code: '.$data[$cols['coupon_code']].'<br />';
        createCoupon(
            $data[$cols['coupon_code']],
			$data[$cols['prefix']],
            $data[$cols['description']],
            'by_percent',
            $data[$cols['discount_amount']]
        );
    } else {
        echo 'Not imported (not active): '.$data[$cols['coupon_code']].'<br />';
    }
}
 
function createCoupon($code,$prefix, $description, $type, $amount, $options = array())
{
  
    $rule = Mage::getModel('salesrule/rule');
    $rule->setName("mannaMunn".$code);
    $rule->setCouponCode($code);
    $rule->setDescription($description);
 
 
    if(!isset($options['from'])) { $options['from'] = date('Y-m-d'); }
 
    $rule->setFromDate($options['from']); // From date
 
    // To date:
    if(isset($options['to'])) {
        $rule->setToDate($options['to']);//if you need an expiration date
    }
 
    $rule->setUsesPerCoupon(1);//number of allowed uses for this coupon
    $rule->setUsesPerCustomer(1);//number of allowed uses for this coupon for each customer
    $rule->setCustomerGroupIds(getAllCustomerGroups());//if you want only certain groups replace getAllCustomerGroups() with an array of desired ids
    $rule->setIsActive(1);
    $rule->setStopRulesProcessing(0);//set to 1 if you want all other rules after this to not be processed
    $rule->setIsRss(0);//set to 1 if you want this rule to be public in rss
    $rule->setIsAdvanced(1);//have no idea what it means :)
    $rule->setProductIds('');
    $rule->setSortOrder(0);// order in which the rules will be applied
 
    $rule->setSimpleAction($type);
 
    $rule->setDiscountAmount($amount);//the discount amount/percent. if SimpleAction is by_percent this value must be <= 100
    $rule->setDiscountQty(1);//Maximum Qty Discount is Applied to
    $rule->setDiscountStep(0);//used for buy_x_get_y; This is X
    $rule->setSimpleFreeShipping(0);//set to 1 for Free shipping
    $rule->setApplyToShipping(0);//set to 0 if you don't want the rule to be applied to shipping
    $rule->setWebsiteIds(72);//mannas website ID
	//we call this method chaining...
	$item_found = Mage::getModel('salesrule/rule_condition_product_found')
      ->setType('salesrule/rule_condition_product_found')
      ->setValue(1) // 1 == FOUND
      ->setAggregator('all'); // match ALL conditions
    $rule->getConditions()->addCondition($item_found);
    $conditions = Mage::getModel('salesrule/rule_condition_product')
      ->setType('salesrule/rule_condition_product')
      ->setAttribute('sku')
      ->setOperator('==')
      ->setValue('KBBSTL');
    $item_found->addCondition($conditions);
    $actions = Mage::getModel('salesrule/rule_condition_product')
      ->setType('salesrule/rule_condition_product')
      ->setAttribute('sku')
      ->setOperator('==')
      ->setValue('KBBSTL');
    $rule->getActions()->addCondition($actions);
	$rule->loadPost($rule->getData());
    $labels = array();
    $labels[0] = $description;
    $rule->setStoreLabels($labels);
 
    $rule->setCouponType(2);
    $rule->save();
}

function getAllCustomerGroups(){
    //get all customer groups, helper function
    $customerGroups = Mage::getModel('customer/group')->getCollection();
    $groups = array();
    foreach ($customerGroups as $group){
        $groups[] = $group->getId();
    }
    return $groups;
}
 
function getAllWbsites(){
    //get all wabsites, helper function
    $websites = Mage::getModel('core/website')->getCollection();
    $websiteIds = array();
    foreach ($websites as $website){
        $websiteIds[] = $website->getId();
    }
    return $websiteIds;
}

/////////////////////////////////////////////////////////////////////////
//Sample Output
/////////////////////////////////////////////////////////////////////////
//Importing coupon with code: 506671885175
//Importing coupon with code: 181918777458
//Importing coupon with code: 932166897434
//Importing coupon with code: 803756674422
//Importing coupon with code: 557831521778
//Importing coupon with code: 917858575579
//Importing coupon with code: 106235214421
//Importing coupon with code: 702978243379
//Importing coupon with code: 626834320776
//Importing coupon with code: 235176316557
//Importing coupon with code: 847438370967
//Importing coupon with code: 994477127696
//Importing coupon with code: 931936201487
//Importing coupon with code: 871358636034
//Importing coupon with code: 717589557819
//Importing coupon with code: 887171910640
//Importing coupon with code: 817028209825
//Importing coupon with code: 993989975582
//Importing coupon with code: 287651897844
//Importing coupon with code: 429922683574
//Importing coupon with code: 676734585380
//......to n records......................
