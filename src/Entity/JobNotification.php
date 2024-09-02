<?php

namespace App\Entity;

use App\Repository\JobNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobNotificationRepository::class)]
class JobNotification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notification_id')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ApiJobs $api_job_id = null;

    #[ORM\Column(length: 255)]
    private ?string $job_type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recepient = null;

    #[ORM\Column(length: 1024)]
    private ?string $message = null;

    #[ORM\Column]
    private ?bool $status = null;

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

    public function getJobType(): ?string
    {
        return $this->job_type;
    }

    public function setJobType(string $job_type): static
    {
        $this->job_type = $job_type;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeImmutable $sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getRecepient(): ?string
    {
        return $this->recepient;
    }

    public function setRecepient(?string $recepient): static
    {
        $this->recepient = $recepient;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }
}
