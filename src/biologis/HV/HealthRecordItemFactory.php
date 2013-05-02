<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

namespace biologis\HV;

use QueryPath\Query;

class HealthRecordItemFactory {

  private static $classNames = array();
  private static $xmlTemplateCache = array();

  public static function getThing($type_or_qp, $version = 0) {
    $thingNames = array_flip(HVRawConnector::$things);
    $typeId = '';

    if ($type_or_qp instanceof Query) {
      $typeId = $type_or_qp->top()->find('type-id')->text();
    }
    elseif (is_string($type_or_qp)) {
      $typeId = HealthRecordItemFactory::getTypeId($type_or_qp);
      $template = __DIR__ . '/HealthRecordItem/XmlTemplates/' . $typeId . '.xml';
      if (is_readable($template)) {
        $type_or_qp = qp(file_get_contents($template), NULL, array('use_parser' => 'xml'));
      }
    }
    else {
      throw new HVClientException('ThingFactory::getThing must be called with a valid thing name or type id or a QueryPath object representing a thing.');
    }

    if ($typeId) {
      if ($type_or_qp instanceof Query) {
        if ($className = HealthRecordItemFactory::convertThingNameToClassName($thingNames[$typeId])) {
          return new $className($type_or_qp);
        }
        else {
          throw new HVClientException('Things of that type id are not supported yet: ' . $typeId);
        }
      }
      else {
        throw new HVClientException('Creation of new empty things of that type id is not supported yet: ' . $typeId);
      }
    }
    else {
      throw new HVClientException('Unable to detect type id.');
    }
  }

  public static function getTypeId($thingNameOrTypeId) {
    if (array_key_exists($thingNameOrTypeId, HVRawConnector::$things)) {
      return HVRawConnector::$things[$thingNameOrTypeId];
    }
    elseif (!in_array($thingNameOrTypeId, HVRawConnector::$things)) {
      throw new HVClientException('Unknown thing name or type id: ' . $thingNameOrTypeId);
    }
    return $thingNameOrTypeId;
  }

  private static function convertThingNameToClassName($thingName) {
    if (!array_key_exists($thingName, HealthRecordItemFactory::$classNames)) {
      $className = preg_replace('/[^a-zA-Z0-9]/', ' ', $thingName);
      $className =
        preg_replace_callback('/\s+(\w)/', function($matches) {
          return strtoupper($matches[1]);
        }, $className);

      $fullClassName = 'biologis\\HV\\HealthRecordItem\\' . $className;

      if (!is_readable(__DIR__ . '/HealthRecordItem/' . $className . '.php')) {
        class_alias('biologis\\HV\\HealthRecordItem\\Thing', $fullClassName);
      }

      HealthRecordItemFactory::$classNames[$thingName] = $fullClassName;
    }

    return HealthRecordItemFactory::$classNames[$thingName];
  }

  private static function convertClassNameToThingName($className) {
    if (in_array($className, HealthRecordItemFactory::$classNames)) {
      $thingNames = array_flip(HealthRecordItemFactory::$classNames);
      return $thingNames[$className];
    }
  }
}
