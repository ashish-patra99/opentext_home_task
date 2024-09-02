<?php

namespace App\Entity;

use App\Repository\ApiJobsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiJobsRepository::class)]
class ApiJobs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $debricked_upload_id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $request_started_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $request_completed_at = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $api_job_type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $api_job_end_point = null;

    #[ORM\Column]
    private ?bool $notification_sent = false;

    #[ORM\Column]
    private ?string $scan_status = null;

    #[ORM\OneToMany(targetEntity: JobFile::class , mappedBy:'ApiJobs', cascade:["persist","remove"])]
    private  Collection $jobFiles;

    /**
     * @var Collection<int, JobNotification>
     */
    #[ORM\OneToMany(mappedBy: 'api_job_id', targetEntity: JobNotification::class, orphanRemoval: true)]
    private Collection $notification_id;

    public function __construct()
    {
        $this->jobFiles = new ArrayCollection();
        $this->notification_id = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFiles(): ? Collection
    {
        return $this->jobFiles;
    }

    /**
     * [Description for addJobFile]
     *
     * @param JobFile $file
     * 
     * @return self
     * 
     */
    public function addJobFile(JobFile $file): self
    {
            if(!$this->jobFiles->contains($file)){
                $this->jobFiles[]= $file;
                $file->setApiJobId($this);
            }
            return $this;
    }

    /**
     * [Description for removeJobFile]
     *
     * @param JobFile $file
     * 
     * @return self
     * 
     */
    public function removeJobFile(JobFile $file): self
    {
            if(!$this->jobFiles->removeElement($file)){
                if($file->getApiJobId() == $this) {
                    $file->setApiJobId(null);
                }
            }
            return $this;
    }

    public function getDebrickedUploadId(): ?string
    {
        return $this->debricked_upload_id;
    }

    public function setDebrickedUploadId(?string $debricked_upload_id): static
    {
        $this->debricked_upload_id = $debricked_upload_id;

        return $this;
    }

    public function getRequestStartedAt(): ?\DateTimeImmutable
    {
        return $this->request_started_at;
    }

    public function setRequestStartedAt(\DateTimeImmutable $request_started_at): static
    {
        $this->request_started_at = $request_started_at;

        return $this;
    }

    public function getRequestCompletedAt(): ?\DateTimeImmutable
    {
        return $this->request_completed_at;
    }

    public function setRequestCompletedAt(?\DateTimeImmutable $request_completed_at): static
    {
        $this->request_completed_at = $request_completed_at;

        return $this;
    }

    public function getApiJobType(): ?string
    {
        return $this->api_job_type;
    }

    public function setApiJobType(?string $api_job_type): static
    {
        $this->api_job_type = $api_job_type;

        return $this;
    }

    public function getApiJobEndPoint(): ?string
    {
        return $this->api_job_end_point;
    }

    public function setApiJobEndPoint(?string $api_job_end_point): static
    {
        $this->api_job_end_point = $api_job_end_point;

        return $this;
    }

    public function isNotificationSent(): ?bool
    {
        return $this->notification_sent;
    }

    public function setNotificationSent(bool $notification_sent): static
    {
        $this->notification_sent = $notification_sent;

        return $this;
    }

    /**
     * @return Collection<int, JobNotification>
     */
    public function getNotificationId(): Collection
    {
        return $this->notification_id;
    }

    public function addNotificationId(JobNotification $notificationId): static
    {
        if (!$this->notification_id->contains($notificationId)) {
            $this->notification_id->add($notificationId);
            $notificationId->setApiJobId($this);
        }

        return $this;
    }

    public function setScanStatus(string $scan_status)
    {
        $this->scan_status = $scan_status;

        return $this;
    }

    public function getScanStatus(): ?string
    {
        return $this->scan_status;
    }

    public function removeNotificationId(JobNotification $notificationId): static
    {
        if ($this->notification_id->removeElement($notificationId)) {
            // set the owning side to null (unless already changed)
            if ($notificationId->getApiJobId() === $this) {
                $notificationId->setApiJobId(null);
            }
        }

        return $this;
    }
}
