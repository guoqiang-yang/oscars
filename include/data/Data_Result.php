<?php

class Data_Result implements ArrayAccess  {

	private	$container = array();

	public function	__construct(array $data, $rownum, $insertid=0, $affectedrows=0)
	{
		$this->container['data'] =	$data;
		$this->container['rownum'] = intval($rownum);
		$this->container['insertid'] = intval($insertid);
		$this->container['affectedrows'] = intval($affectedrows);
	}

	/**
	 * ArrayAccess（数组式访问）接口实现
	 */
	public function	offsetSet($offset, $value)
	{
		if (is_null($offset))
		{
			$this->container[] = $value;
		}
		else
		{
			$this->container[$offset] =	$value;
		}
	}

	/**
	 * ArrayAccess（数组式访问）接口实现
	 */
	public function	offsetExists($offset)
	{
		return isset($this->container[$offset]);
	}

	/**
	 * ArrayAccess（数组式访问）接口实现
	 */
	public function	offsetUnset($offset)
	{
		unset($this->container[$offset]);
	}

	/**
	 * ArrayAccess（数组式访问）接口实现
	 */
	public function	offsetGet($offset)
	{
		return isset($this->container[$offset])	? $this->container[$offset]	: null;
	}

	public function getValues($field)
	{
		$field = trim($field);
		assert(!empty($field));

		$result = array();
		if (!empty($this->container['data']))
		{
			foreach ($this->container['data'] as $row)
			{
				$result[] = $row[$field];
			}
		}
		return $result;
	}

	public function getMap($key, $orderKeys = array())
	{
		$map = array();
		foreach($this->container['data'] as $item)
		{
			$map[$item[$key]] =	$item;
		}
		if (!empty($orderKeys))
		{
			$tmpMap = array();
			foreach ($orderKeys as $key)
			{
				if (isset($map[$key]))
				{
					$tmpMap[$key] = $map[$key];
				}
			}
			$map = $tmpMap;
		}
		return $map;
	}

	public function getFirst()
	{
		$result = array();
		if (!empty($this->container['data']))
		{
			$result = $this->container['data'][0];
		}
		return $result;
	}

	public function isEmpty()
	{
		return empty($this->container['data']);
	}

}
