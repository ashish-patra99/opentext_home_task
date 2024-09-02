<?php

namespace App\Entity;

use App\Repository\JobFileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobFileRepository::class)]
class JobFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ApiJobs $api_job_id = null;

    #[ORM\Column(length: 255)]
    private ?string $file_name = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $scanned_at = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $ciUploadId = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $remark = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiJobId(): ?ApiJobs
    {
        return $this->api_job_id;
    }

    public function setApiJobId(?ApiJobs $api_job_id): static
    {
        $this->api_job_id = $api_job_id;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name): static
    {
        $this->file_name = $file_name;

        return $this;
    }

    public function getScannedAt(): ?\DateTimeImmutable
    {
        return $this->scanned_at;
    }

    public function setScannedAt(?\DateTimeImmutable $scanned_at): static
    {
        $this->scanned_at = $scanned_at;

        return $this;
    }

    public function getCiUploadId(): ?string
    {
        return $this->ciUploadId;
    }

    public function setCiUploadId(?string $ciUploadId): static
    {
        $this->ciUploadId = $ciUploadId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): static
    {
        $this->remark = $remark;

        return $this;
    }
}
