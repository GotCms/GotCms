<?php
/**
 * @author Rambaud Pierre
 *
 */
namespace Es\Component;

interface IterableInterface {

    /**
     * @return array
     */
    public function getChildren();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return Object
     */
    public function getParent();

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return string
     */
    public function getIterableId();

    /**
     * @return string
     */
    public function getIcon();
}
