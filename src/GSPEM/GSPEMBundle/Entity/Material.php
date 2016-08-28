<?php

namespace GSPEM\GSPEMBundle\Entity;

/**
 * Material
 */
class Material
{
    /**
     * @var int
     */
    private $id;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $descript;

    /**
     * @var integer
     */
    private $type;

    /**
     * @var integer
     */
    private $idCustom;

    /**
     * @var string
     */
    private $img;

    /**
     * @var \DateTime
     */
    private $date;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return Material
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set descript
     *
     * @param string $descript
     *
     * @return Material
     */
    public function setDescript($descript)
    {
        $this->descript = $descript;

        return $this;
    }

    /**
     * Get descript
     *
     * @return string
     */
    public function getDescript()
    {
        return $this->descript;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Material
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set idCustom
     *
     * @param integer $idCustom
     *
     * @return Material
     */
    public function setIdCustom($idCustom)
    {
        $this->idCustom = $idCustom;

        return $this;
    }

    /**
     * Get idCustom
     *
     * @return integer
     */
    public function getIdCustom()
    {
        return $this->idCustom;
    }

    /**
     * Set img
     *
     * @param string $img
     *
     * @return Material
     */
    public function setImg($img)
    {
        $this->img = $img;

        return $this;
    }

    /**
     * Get img
     *
     * @return string
     */
    public function getImg()
    {
        return $this->img;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Material
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}
