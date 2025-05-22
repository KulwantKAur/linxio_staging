<?php

namespace App\Service\Notification;

use App\Entity\Notification\Event;
use App\Entity\Notification\EventTemplate;
use App\Entity\Notification\Notification;
use App\Entity\Notification\Template;
use App\Entity\Notification\TemplateSet;
use App\Entity\Setting;
use App\Entity\Team;
use Doctrine\ORM\EntityManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class TemplateService
{
    protected const PLACEHOLDER_TEMPLATE = '${%s}';
    protected const COMMENT_BY_TEMPLATE = 'Comment: ';

    private $templateSetRepository;
    private $eventTemplateRepository;
    private TranslatorInterface $translator;

    public function __construct(EntityManager $em, TranslatorInterface $translator)
    {
        $this->templateSetRepository = $em->getRepository(TemplateSet::class);
        $this->eventTemplateRepository = $em->getRepository(EventTemplate::class);
        $this->translator = $translator;
    }

    public function getPreparedTemplates(
        Team $team,
        Notification $notification,
        $allowedTransports,
        $placeholders
    ): array {
        $preparedTemplates = [];
        $this->translator->setLocale($notification->getLanguageValue());
        foreach (
            $this->getRecipientEventTemplates(
                $team,
                $notification->getEvent(),
                $allowedTransports
            ) as $template
        ) {
            $preparedTemplates[] = [
                $this->generate(
                    $template,
                    array_merge(
                        $notification->toArray(Notification::NOTIFICATION_PLACEHOLDERS, $this->translator),
                        $placeholders
                    )
                ),
                $template,
            ];
        }

        return $preparedTemplates;
    }

    /**
     * @param Team $team
     * @param Event $event
     * @param array $allowedTransports
     * @return Template[]|array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getRecipientEventTemplates(Team $team, Event $event, array $allowedTransports): array
    {
        $templateSet = $this->templateSetRepository->getByTeam($team);

        return $this->eventTemplateRepository->getEventTemplates($templateSet, $event, $allowedTransports);
    }

    /**
     * @param Template $template
     * @param array $placeholders
     * @return array
     */
    public function generate(Template $template, array $placeholders): array
    {
        $preparedBody = [];

        foreach ($template->getBodyTranslateKeys() as $prop => $tplKey) {
            $tpl = $this->translator->trans($tplKey, [], Template::TRANSLATE_DOMAIN);
            $preparedBody[$prop] = $this->replace($tpl, $placeholders);
        }

        return $preparedBody;
    }

    /**
     * @param string $tpl
     * @param array $placeholders
     * @return string
     */
    private function replace(string $tpl, array $placeholders): string
    {
        return str_replace(
            array_map(
                static function ($v) {
                    return sprintf(self::PLACEHOLDER_TEMPLATE, $v);
                },
                array_keys($placeholders)
            ),
            array_values($placeholders),
            $tpl
        );
    }
}
