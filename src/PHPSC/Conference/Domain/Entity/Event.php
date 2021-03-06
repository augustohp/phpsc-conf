<?php
namespace PHPSC\Conference\Domain\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use PHPSC\Conference\Infra\Persistence\Entity;

/**
 * @Entity(repositoryClass="PHPSC\Conference\Domain\Repository\EventRepository")
 * @Table(
 *     "event",
 *     indexes={
 *         @Index(name="event_index0", columns={"start"}),
 *         @Index(name="event_index1", columns={"submissions_start", "submissions_end"})
 *     }
 * )
 *
 * @author Luís Otávio Cobucci Oblonczyk <lcobucci@gmail.com>
 */
class Event implements Entity
{
    /**
     * @Id
     * @Column(type="integer")
     * @generatedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="string", length=80, nullable=false)
     *
     * @var string
     */
    private $name;

    /**
     * @ManyToOne(targetEntity="Location", cascade={"all"})
     * @JoinColumn(name="location_id", referencedColumnName="id", nullable=false)
     *
     * @var Location
     */
    private $location;

    /**
     * @OneToOne(targetEntity="RegistrationInfo", mappedBy="event")
     *
     * @var RegistrationInfo
     */
    private $registrationInfo;

    /**
     * @ManyToMany(targetEntity="User")
     * @JoinTable(name="evaluator",
     *      joinColumns={@JoinColumn(name="event_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection
     */
    private $evaluators;

    /**
     * @Column(type="date", nullable=false, name="start")
     *
     * @var DateTime
     */
    private $startDate;

    /**
     * @Column(type="date", nullable=false, name="end")
     *
     * @var DateTime
     */
    private $endDate;

    /**
     * @Column(type="datetime", name="submissions_start", nullable=true)
     *
     * @var DateTime
     */
    private $submissionStart;

    /**
     * @Column(type="datetime", name="submissions_end", nullable=true)
     *
     * @var DateTime
     */
    private $submissionEnd;

    /**
     * @ManyToOne(targetEntity="Logo", cascade={"all"})
     * @JoinColumn(name="logo_id", referencedColumnName="id", nullable=true)
     *
     * @var Logo
     */
    private $logo;

    /**
     * Inicializa o objeto
     */
    public function __construct()
    {
        $this->evaluators = new ArrayCollection();
    }


    /**
     * @return number
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param number $id
     */
    public function setId($id)
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('O id deve ser maior ou igual à ZERO');
        }

        $this->id = (int) $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        if (empty($name)) {
            throw new InvalidArgumentException('O nome não pode ser vazio');
        }

        $this->name = (string) $name;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Location $location
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;
    }

    /**
     * @return RegistrationInfo
     */
    public function getRegistrationInfo()
    {
        return $this->registrationInfo;
    }

    /**
     * @param RegistrationInfo $registrationInfo
     */
    public function setRegistrationInfo(RegistrationInfo $registrationInfo)
    {
        $this->registrationInfo = $registrationInfo;
    }

    /**
     * @return ArrayCollection
     */
    public function getEvaluators()
    {
        return $this->evaluators;
    }

    /**
     * @param ArrayCollection $evaluators
     */
    public function setEvaluators(ArrayCollection $evaluators)
    {
        $this->evaluators = $evaluators;
    }

    /**
     * @param User $user
     * @return boolean
     */
    public function isEvaluator(User $user)
    {
        return $this->getEvaluators()->contains($user);
    }

    /**
     * @return boolean
     */
    public function hasAttendeeRegistration()
    {
        return $this->getRegistrationInfo() !== null;
    }

    /**
     * @return DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param DateTime $startDate
     */
    public function setStartDate(DateTime $startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param DateTime $endDate
     */
    public function setEndDate(DateTime $endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @param DateTime $date
     * @return boolean
     */
    public function isRegistrationPeriod(DateTime $date)
    {
        if (!$this->hasAttendeeRegistration()) {
            return false;
        }

        return $date >= $this->getRegistrationInfo()->getStart()
               && $date <= $this->getRegistrationInfo()->getEnd();
    }

    /**
     * @param DateTime $now
     * @return boolean
     */
    public function isEventPeriod(DateTime $now)
    {
        $end = new DateTime($this->getEndDate()->format('Y/m/d') . ' 23:59:59');

        return $now >= $this->getStartDate() && $now <= $end;
    }

    /**
     * @param DateTime $now
     * @return boolean
     */
    public function isLateRegistrationPeriod(DateTime $now)
    {
        return $this->isRegistrationPeriod($now) && $this->isEventPeriod($now);
    }

    /**
     * @param DateTime $now
     * @return boolean
     */
    public function isRegularRegistrationPeriod(DateTime $now)
    {
        return $this->isRegistrationPeriod($now) && !$this->isEventPeriod($now);
    }

    /**
     * @return DateTime
     */
    public function getSubmissionStart()
    {
        return $this->submissionStart;
    }

    /**
     * @param DateTime $submissionStart
     */
    public function setSubmissionStart(DateTime $submissionStart = null)
    {
        $this->submissionStart = $submissionStart;
    }

    /**
     * @return DateTime
     */
    public function getSubmissionEnd()
    {
        return $this->submissionEnd;
    }

    /**
     * @param DateTime $submissionEnd
     */
    public function setSubmissionEnd(DateTime $submissionEnd = null)
    {
        $this->submissionEnd = $submissionEnd;
    }

    /**
     * @return boolean
     */
    public function hasTalkSubmissions()
    {
        return $this->getSubmissionStart() !== null;
    }

    /**
     * @param DateTime $date
     * @return boolean
     */
    public function isSubmissionsPeriod(DateTime $date)
    {
        if (!$this->hasTalkSubmissions()) {
            return false;
        }

        return $date >= $this->getSubmissionStart()
               && $date <= $this->getSubmissionEnd();
    }

    /**
     * @return DateTime
     */
    public function getTalkEvaluationStart()
    {
        if (!$this->hasTalkSubmissions()) {
            return null;
        }

        $evaluationStart = clone $this->getSubmissionEnd();
        $evaluationStart->modify('+1 second');

        return $evaluationStart;
    }

    /**
     * @return DateTime
     */
    public function getTalkEvaluationEnd()
    {
        if (!$this->hasTalkSubmissions()) {
            return null;
        }

        $evaluationEnd = clone $this->getSubmissionEnd();
        $evaluationEnd->modify('+7 days');

        return $evaluationEnd;
    }

    /**
     * @param DateTime $date
     * @return boolean
     */
    public function isSpeakerPromotionalPeriod(DateTime $date)
    {
        if ($evaluationEnd = $this->getTalkEvaluationEnd()) {
            $promotionEnd = clone $evaluationEnd;
            $promotionEnd->modify('+7 days');

            return $date > $evaluationEnd && $date <= $promotionEnd;
        }

        return false;
    }

    /**
     * @return Logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param Logo $logo
     */
    public function setLogo(Logo $logo = null)
    {
        $this->logo = $logo;
    }

    /**
     * @return boolean
     */
    public function hasLogo()
    {
        return $this->getLogo() !== null;
    }
}
