<?php

namespace SearchHistory\Entity;

use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Site;
use Omeka\Entity\User;

/**
 * @Entity
 * @Table(
 *     name="search_history"
 * )
 * @HasLifecycleCallbacks
 */
class SearchHistory extends AbstractEntity
{
    /**
     * @var int
     *
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var User
     *
     * @ManyToOne(
     *      targetEntity="\Omeka\Entity\User"
     * )
     * @JoinColumn(
     *      nullable=false,
     *      onDelete="CASCADE"
     * )
     */
    protected $user;

    /**
     * @var string
     *
     * @Column(
     *     type="string",
     *     nullable=true,
     *     length=190
     * )
     */
    protected $comment;

    /**
     * @var \Omeka\Entity\Site
     *
     * @ManyToOne(
     *      targetEntity="\Omeka\Entity\Site"
     * )
     * @JoinColumn(
     *      nullable=true,
     *      onDelete="CASCADE"
     * )
     */
    protected $site;

    /**
     * @var string
     *
     * @Column(
     *     type="string",
     *     nullable=true,
     *     length=190
     * )
     */
    protected $engine;

    /**
     * @var string
     *
     * @Column(
     *      type="text",
     *      nullable=true
     * )
     */
    protected $query;

    /**
     * @var DateTime
     *
     * @Column(type="datetime")
     */
    protected $created;

    /**
     * @var DateTime
     *
     * @Column(type="datetime")
     */
    protected $modified;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param User $user
     * @return self
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \Omeka\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $comment
     * @return self
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param Site $site
     * @return self
     */
    public function setSite(Site $site = null)
    {
        $this->site = $site;
        return $this;
    }

    /**
     * @return \Omeka\Entity\Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param string $engine
     * @return self
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param string $query
     * @return self
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param DateTime $created
     * @return self
     */
    public function setCreated(DateTime $created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param DateTime $modified
     * @return self
     */
    public function setModified(DateTime $modified)
    {
        $this->modified = $modified;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @PrePersist
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->created = new DateTime('now');
        return $this;
    }
}
