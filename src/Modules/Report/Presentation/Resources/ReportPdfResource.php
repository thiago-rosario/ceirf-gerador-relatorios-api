<?php

declare(strict_types=1);

namespace src\Modules\Report\Presentation\Resources;

use src\Modules\Report\Application\DTO\ReportAttachmentDataDTO;
use src\Modules\Report\Application\DTO\ReportDataDTO;
use src\Modules\Report\Application\DTO\ReportImageDataDTO;

/**
 * @phpstan-type PdfFigure array{key: string, anchor: string, number: int, caption: string, sources: list<string>}
 * @phpstan-type PdfSection array{anchor: string, title: string}
 */
readonly class ReportPdfResource
{
    /** @param array<string, list<string>> $media */
    public function __construct(public ReportDataDTO $report, private array $media = []) {}

    /**
     * @return array{report: ReportDataDTO, sections: list<PdfSection>, figures: list<PdfFigure>, infrastructure: array<string, string|null>, checklist: array<string, string|null>}
     */
    public function toArray(): array
    {
        return [
            'report' => $this->report,
            'sections' => $this->sections(),
            'figures' => $this->figures(),
            'infrastructure' => $this->answers($this->report->infrastructure, [
                'waterNetwork' => 'Rede de água',
                'highVoltageNetwork' => 'Rede de alta tensão',
                'lowVoltageNetwork' => 'Rede de baixa tensão',
                'sewageNetwork' => 'Rede de esgoto',
                'telephony' => 'Telefonia',
                'publicLighting' => 'Iluminação pública',
                'internet' => 'Internet',
                'wasteCollection' => 'Coleta de resíduos',
                'paving' => 'Pavimentação',
                'existingBuildings' => 'Edificações existentes',
            ]),
            'checklist' => $this->answers($this->report->attachments?->checklist, [
                'seiConstructionRequest' => 'Solicitação de construção no SEI',
                'seiLandAndTypologyIdentification' => 'Identificação do terreno e da tipologia no SEI',
                'stateOwnedLand' => 'Terreno de propriedade do Estado',
                'simovLegalized' => 'Regularização no SIMOV',
                'compatibleDimensions' => 'Dimensões compatíveis com a implantação',
                'slopeOrLevelRisk' => 'Risco decorrente de declividade ou desnível',
                'stormwaterDrainage' => 'Drenagem de águas pluviais',
                'floodHistory' => 'Histórico de alagamentos',
                'electricitySupply' => 'Abastecimento de energia elétrica',
                'waterSupply' => 'Abastecimento de água',
                'sewageSupply' => 'Atendimento por rede de esgoto',
                'pavingAndSidewalk' => 'Pavimentação e passeio',
                'regularWasteCollection' => 'Coleta regular de resíduos',
                'demolitionRequired' => 'Necessidade de demolição',
                'easyPublicAccess' => 'Facilidade de acesso do público',
                'domainStripOrNonBuildableArea' => 'Faixa de domínio ou área não edificável',
                'technicalFeasibilityReport' => 'Relatório de viabilidade técnica',
                'reportAttachedToSei' => 'Relatório anexado ao SEI',
                'worksDashboardUpdated' => 'Painel de obras atualizado',
                'environmentalProtectionArea' => 'Área de proteção ambiental',
            ]),
        ];
    }

    /** @param array<string, int> $pages */
    public function render(array $pages = []): string
    {
        return view('report::pdf', [...$this->toArray(), 'pages' => $pages])->render();
    }

    /** @return list<PdfSection> */
    private function sections(): array
    {
        $titles = [
            'general-information' => '1. Informações Gerais',
            'objective' => '2. Objetivo',
            'location' => '3. Localização do Terreno',
            'conditions' => '4. Condições do Terreno',
            'infrastructure' => '5. Infraestrutura Existente',
            'pre-implementation' => '6. Sugestão de Pré-implantação',
            'photographs' => '7. Documentação Fotográfica',
            'conclusion' => '8. Conclusão',
            'attachments' => '9. Anexos',
            'checklist' => '9.1. Checklist do Terreno',
        ];

        if ($this->report->attachments?->municipalityLocationMap !== null) {
            $titles['municipality-map'] = 'Mapa de Localização do Município';
        }

        if ($this->report->attachments?->topographicPlan !== null) {
            $titles['topographic-plan'] = 'Planta Topográfica';
        }

        foreach ($this->report->attachments->others ?? [] as $attachment) {
            $titles['attachment-'.$attachment->id] = $attachment->description;
        }

        $sections = [];

        foreach ($titles as $anchor => $title) {
            $sections[] = ['anchor' => $anchor, 'title' => $title];
        }

        return $sections;
    }

    /** @return list<PdfFigure> */
    private function figures(): array
    {
        $figures = [];
        $this->appendFigure($figures, 'location-map', $this->report->location?->locationMap, 'Mapa de Localização');
        $this->appendFigure($figures, 'state-map', $this->report->location?->municipalityInStateMap, 'Localização do município em relação ao estado da Bahia');
        $this->appendFigure($figures, 'pre-implementation-image', $this->report->preImplementation?->image, 'Sugestão de pré-implantação COTEC');

        foreach ($this->report->photographicDocumentation->images ?? [] as $image) {
            $this->appendFigure($figures, 'photograph-'.$image->id, $image, $image->caption);
        }

        $this->appendFigure($figures, 'municipality-map', $this->report->attachments?->municipalityLocationMap, 'Mapa de Localização do Município');
        $this->appendFigure($figures, 'topographic-plan', $this->report->attachments?->topographicPlan, 'Planta Topográfica');

        foreach ($this->report->attachments->others ?? [] as $attachment) {
            $this->appendFigure($figures, 'attachment-'.$attachment->id, $attachment, $attachment->description);
        }

        return $figures;
    }

    /** @param list<PdfFigure> $figures */
    private function appendFigure(array &$figures, string $key, ReportImageDataDTO|ReportAttachmentDataDTO|null $image, string $caption): void
    {
        if ($image === null) {
            return;
        }

        $number = count($figures) + 1;
        $figures[] = [
            'key' => $key,
            'anchor' => 'figure-'.$number,
            'number' => $number,
            'caption' => $caption,
            'sources' => $this->media[$image->file->storageIdentifier] ?? [],
        ];
    }

    /**
     * @param array<string, string> $labels
     * @return array<string, string|null>
     */
    private function answers(?object $section, array $labels): array
    {
        $answers = [];

        foreach ($labels as $property => $label) {
            $answers[$label] = $section?->{$property};
        }

        return $answers;
    }
}
