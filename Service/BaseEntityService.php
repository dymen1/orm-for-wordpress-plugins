<?php
namespace Dorans\Competition\Service;

use Dorans\Competition\Repository\BaseEntityRepository;
use Dorans\Competition\Util\EntityMapper;
use Exception;

class BaseEntityService
{
    public function delete($entityClass)
    {
        global $wpdb;
        $id = $_POST['id'];
        $repository = new BaseEntityRepository($wpdb, $entityClass);
        try {
            $result = $repository->delete($id);
            if (!$result) {
                throw new Exception('Something went wrong whilst trying to delete "' . $entityClass . '" with id: ' . $id);
            }
        } catch (Exception $e) {
            echo '<span>' . $e->getMessage() . '</span>';
        }
    }

    public function save($entityClass)
    {
        global $wpdb;
        $repository = new BaseEntityRepository($wpdb, $entityClass);
        try {
            $entity = (new EntityMapper($entityClass))->hydrate($_POST);
            $result = $repository->persist($entity);
            if ($result === false) {
                throw new Exception('Something went wrong whilst trying to save "' . $entityClass . '"');
            }
        } catch (Exception $e) {
            echo '<span>' . $e . '</span>';
        }
    }

    public function getAll($entityClass)
    {
        global $wpdb;
        $repository = new BaseEntityRepository($wpdb, $entityClass);
        try {
            return $repository->findAll();
        } catch (Exception $e) {
            echo '<span>' . $e . '</span>';
            return array();
        }
    }

    public function find($id, $entityClass)
    {
        global $wpdb;
        $repository = new BaseEntityRepository($wpdb, $entityClass);
        try {
            return $repository->find($id);
        } catch (Exception $e) {
            echo '<span>' . $e . '</span>';
            return array();
        }
    }
}