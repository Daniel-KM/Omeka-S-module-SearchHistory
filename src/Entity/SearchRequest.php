<?php declare(strict_types=1);

namespace SearchHistory\Entity;

use DateTime;
use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Site;
use Omeka\Entity\User;

/**
 * @Entity
 * @Table(
 *     name="search_request"
 * )
 */
class SearchRequest extends AbstractEntity
{
    /**
     * @var int
     *
     * @Id
     * @Column(
     *     type="integer"
     * )
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
     *     nullable=true,
     *     onDelete="SET NULL"
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
     *     onDelete="SET NULL"
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
     * @Column(
     *      type="datetime",
     *      nullable=false
     * )
     */
    protected $created;

    /**
     * @var DateTime
     *
     * @Column(
     *      type="datetime",
     *      nullable=true
     * )
     */
    protected $modified;

    public function getId()
    {
        return $this->id;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setSite(?Site $site = null): self
    {
        $this->site = $site;
        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setEngine(?string $engine): self
    {
        $this->engine = $engine;
        return $this;
    }

    public function getEngine(): ?string
    {
        return $this->engine;
    }

    public function setQuery(?string $query): self
    {
        $this->query = $query;
        return $this;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;
        return $this;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setModified(?DateTime $modified): self
    {
        $this->modified = $modified;
        return $this;
    }

    public function getModified(): ?DateTime
    {
        return $this->modified;
    }
}
