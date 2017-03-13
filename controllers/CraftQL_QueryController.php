<?php

namespace Craft;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\GraphQL;
use GraphQL\Schema;

require_once rtrim(__DIR__, '/').'/../vendor/autoload.php';

class CraftQL_QueryController extends BaseController
{
    protected $allowAnonymous = true;

    function actionQuery()
    {
        $rawInput = file_get_contents('php://input');

        // Eager load some things we know we'll need later
        craft()->craftQL_schemaTagGroup->loadAllGroups();
        craft()->craftQL_schemaSection->loadAllSections();
        craft()->craftQL_schemaAssetSource->loadAllSources();

        $queryTypeConfig = [
            'name' => 'Query',
            'fields' => [
                'me' => [
                    'type' => Type::string(),
                    'resolve' => function ($root, $args) {
                      return 'wooot!';
                    }
                ]
            ],
        ];

        foreach (craft()->craftQL_schemaSection->loadedSections() as $handle => $sectionType) {
            $args = [
                'id' => Type::int(),
                'limit' => Type::int(),
                'order' => Type::string(),
            ];
            $queryTypeConfig['fields'][$handle] = [
                'type' => Type::listOf($sectionType),
                'description' => 'list of entries',
                'args' => $args,
                'resolve' => function ($root, $args) use ($handle) {
                    $criteria = craft()->elements->getCriteria(ElementType::Entry);
                    $criteria = $criteria->section($handle);
                    foreach ($args as $key => $value) {
                        $criteria = $criteria->{$key}($value);
                    }
                    return $criteria->find();
                }
            ];
        }

        $queryType = new ObjectType($queryTypeConfig);

        $schema = new Schema([
            'query' => $queryType
        ]);

        try {
            $result = GraphQL::execute($schema, $rawInput, []);
        } catch (\Exception $e) {
            $result = [
                'error' => [
                    'message' => $e->getMessage()
                ]
            ];
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($result);
    }
}