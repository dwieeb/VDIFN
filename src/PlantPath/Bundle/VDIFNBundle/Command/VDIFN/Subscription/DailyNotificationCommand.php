<?php

namespace PlantPath\Bundle\VDIFNBundle\Command\VDIFN\Subscription;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PlantPath\Bundle\VDIFNBundle\Geo\Crop;
use PlantPath\Bundle\VDIFNBundle\Geo\Pest;
use PlantPath\Bundle\VDIFNBundle\Geo\Disease;
use PlantPath\Bundle\VDIFNBundle\Geo\Threshold;
use PlantPath\Bundle\VDIFNBundle\Geo\Infliction;
use PlantPath\Bundle\VDIFNBundle\Geo\Model\AbstractModel;
use PlantPath\Bundle\VDIFNBundle\Geo\Model\DiseaseModelData;

class DailyNotificationCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('vdifn:subscription:daily-notification')
            ->setDescription('Send out daily notifications for subscriptions to DSV data');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger = $this->getContainer()->get('logger');
        $date = new \DateTime();

        $this->em = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager');

        // http://konradpodgorski.com/blog/2013/01/18/how-to-avoid-memory-leaks-in-symfony-2-commands
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->subscriptionRepo = $this->em->getRepository('PlantPathVDIFNBundle:Subscription');
        $this->weatherDailyRepo = $this->em->getRepository('PlantPathVDIFNBundle:Weather\Daily');

        $subscriptions = $this
            ->subscriptionRepo
            ->createQueryBuilder('s')
            ->getQuery()
            ->iterate();

        foreach ($subscriptions as $result) {
            $subscription = $result[0];

            $result = $this->weatherDailyRepo
                ->createQueryBuilder('d')
                ->select('d.latitude, d.longitude, SUM(d.dsv) AS dsv')
                ->where('d.time BETWEEN :start AND :end')
                ->andWhere('d.latitude = :latitude')
                ->andWhere('d.longitude = :longitude')
                ->groupBy('d.latitude, d.longitude')
                ->setParameter('start', $subscription->getEmergenceDate())
                ->setParameter('end', $date)
                ->setParameter('latitude', $subscription->getLatitude())
                ->setParameter('longitude', $subscription->getLongitude())
                ->getQuery()
                ->getOneOrNullResult();

            if ($result) {
                $class = AbstractModel::getModelClassByCropAndInfliction(
                    $subscription->getCrop(),
                    $subscription->getInfliction()
                );

                $data = new DiseaseModelData();
                $data->setDayTotal($result['dsv']);
                $actualThreshold = $class::determineThreshold($data);

                if (0 >= Threshold::cmp($subscription->getThreshold(), $actualThreshold)) {
                    $body = $this->getContainer()->get('templating')->render('PlantPathVDIFNBundle:Command:SubscriptionNotification/notification.html.twig', [
                        'subscription' => $subscription,
                        'crop' => Crop::getNameBySlug($subscription->getCrop()),
                        'infliction' => Infliction::getNameBySlug($subscription->getInfliction()),
                        'actualThreshold' => Threshold::getNameBySlug($actualThreshold),
                        'subscriptionThreshold' => Threshold::getNameBySlug($subscription->getThreshold()),
                    ]);

                    $message = \Swift_Message::newInstance()
                        ->setSubject('Threshold reached!')
                        ->setFrom($this->getContainer()->getParameter('vdifn.admin.email'))
                        ->setTo($subscription->getUser()->getEmail())
                        ->setBody($body, 'text/html');

                    $this->getContainer()->get('mailer')->send($message);
                }
            }
        }
    }
}
