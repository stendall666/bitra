<?php
use Bitrix\Main\Loader;
use Bitrix\Rest\RestException;

class ContactDealsRest
{

    public static function OnRestServiceBuildDescription()
    {
        return array(
            'crm' => array(
                'contact.deals.get' => array(
                    'callback' => array(__CLASS__, 'getContactDeals'),
                    'options' => array(),
                ),
            )
        );
    }

    public static function getContactDeals($query, $n, \CRestServer $server)
    {

        if ($query['contact_id'] <= 0 || !is_numeric($query['contact_id'])) {
            throw new RestException(
                'Id контакта должен быть положительным , не нулевым числом',
                'INVALID_CONTACT_ID',
                \CRestServer::STATUS_WRONG_REQUEST
            );
        }

        $contactId = (int) $query['contact_id'];
        try {
            $deals = self::getDealsByContactId($contactId);
            return array(
                'contact_id' => $contactId,
                'deals_id' => $deals,
                'deals_count' => count($deals)
            );

        } catch (Exception $e) {
            throw new RestException(
                $e->getMessage(),
                'INTERNAL_ERROR',
                \CRestServer::STATUS_INTERNAL
            );
        }
    }

    private static function getDealsByContactId($contactId)
    {
        $deals = [];
        $arrRes = \Bitrix\Crm\DealTable::getList([
            'select' => ['ID'],
            'filter' => [
                'CONTACT_ID' => $contactId,

            ],
            'order' => ['ID' => 'ASC']
        ]);
        while ($deal = $arrRes->fetch()) {
            $deals[] = $deal['ID'];
        }
        return $deals;
    }

}

AddEventHandler(
    'rest',
    'OnRestServiceBuildDescription',
    [
        '\ContactDealsRest',
        'OnRestServiceBuildDescription'
    ]
);