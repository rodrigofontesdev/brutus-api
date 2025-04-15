<?php

use App\Events\AnnualRevenueChanged;
use App\Models\MeiCategory;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Carbon;

describe('Annual Revenue Changed', function() {
    beforeEach(function() {
        $this->subscriber = User::factory()->create();
        $this->actingAs($this->subscriber);

        $this->travelTo(now()->setYear(2025));
        $this->currentYear = now()->year;
    });

    it('should return an empty array if the MEI opening date hasn\'t been provided by the subscriber',
        function () {
            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toBeEmpty();
        }
    );

    it('should return the annual revenues starting from the MEI opening date through the current year',
        function() {
            MeiCategory::factory()->for($this->subscriber, 'owner')->create([
                'type' => 'MEI-GERAL',
                'creation_date' => Carbon::createFromDate(2023, 1, 1)
            ]);

            $annualRevenues = [
                [
                    'year' => $this->currentYear,
                    'total' => 0,
                    'limit' => 8_100_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2024,
                    'total' => 0,
                    'limit' => 8_100_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2023,
                    'total' => 0,
                    'limit' => 8_100_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
            ];

            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toHaveLength(3)->toMatchArray($annualRevenues);
        }
    );

    it('should return the accurate annual revenue totals for each year', function() {
        MeiCategory::factory()->for($this->subscriber, 'owner')->create([
            'type' => 'MEI-GERAL',
            'creation_date' => Carbon::createFromDate(2023, 1, 1)
        ]);
        Report::factory()
            ->for($this->subscriber, 'owner')
            ->count(3)
            ->sequence(
                ['period' => Carbon::createFromDate(2024, 3, 1)],
                ['period' => Carbon::createFromDate(2024, 6, 1)],
                ['period' => Carbon::createFromDate(null, 1, 1)],
            )->create([
                'trade_with_invoice' => 0,
                'trade_without_invoice' => 0,
                'industry_with_invoice' => 100_000,
                'industry_without_invoice' => 0,
                'services_with_invoice' => 0,
                'services_without_invoice' => 100_000,
            ]);

        $annualRevenues = [
            [
                'year' => $this->currentYear,
                'total' => 200_000,
                'limit' => 8_100_000,
                'limit_exceeded' => false,
                'status' => 'below'
            ],
            [
                'year' => 2024,
                'total' => 400_000,
                'limit' => 8_100_000,
                'limit_exceeded' => false,
                'status' => 'below'
            ],
            [
                'year' => 2023,
                'total' => 0,
                'limit' => 8_100_000,
                'limit_exceeded' => false,
                'status' => 'below'
            ],
        ];

        $event = new AnnualRevenueChanged;
        $payload = $event->broadcastWith();

        expect($payload)->toHaveLength(3)->toMatchArray($annualRevenues);
    });

    it('should return the appropriate annual limit for each MEI category',
        function (string $category) {
            MeiCategory::factory()->for($this->subscriber, 'owner')->create([
                'type' => $category,
                'creation_date' => Carbon::createFromDate(null, 1, 1)
            ]);

            $limit = match($category) {
                'MEI-GERAL' => 8_100_000,
                'MEI-TAC' => 25_160_000,
            };

            $annualRevenues = [
                [
                    'year' => $this->currentYear,
                    'total' => 0,
                    'limit' => $limit,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ]
            ];

            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toMatchArray($annualRevenues);
        }
    )->with(['MEI-GERAL', 'MEI-TAC']);

    it('should return the appropriate annual limit for each MEI category in the opening year',
        function (string $category) {
            MeiCategory::factory()->for($this->subscriber, 'owner')->create([
                'type' => $category,
                'creation_date' => Carbon::createFromDate(2024, 6, 1)
            ]);

            $limit = match($category) {
                'MEI-GERAL' => 8_100_000,
                'MEI-TAC' => 25_160_000,
            };

            $limitInOpeningYear = match($category) {
                'MEI-GERAL' => 4_725_000,
                'MEI-TAC' => 14_676_669,
            };

            $annualRevenues = [
                [
                    'year' => $this->currentYear,
                    'total' => 0,
                    'limit' => $limit,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2024,
                    'total' => 0,
                    'limit' => $limitInOpeningYear,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
            ];

            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toHaveLength(2)->toMatchArray($annualRevenues);
        }
    )->with(['MEI-GERAL', 'MEI-TAC']);

    it('should return a "below" status if the annual revenue limit hasn\'t been exceeded',
        function(string $category) {
            MeiCategory::factory()->for($this->subscriber, 'owner')->create([
                'type' => $category,
                'creation_date' => Carbon::createFromDate(null, 1, 1)
            ]);
            Report::factory()
                ->for($this->subscriber, 'owner')
                ->create([
                    'trade_with_invoice' => 1_000_000,
                    'trade_without_invoice' => 0,
                    'industry_with_invoice' => 0,
                    'industry_without_invoice' => 0,
                    'services_with_invoice' => 0,
                    'services_without_invoice' => 0,
                    'period' => Carbon::createFromDate(null, 2, 1)
                ]);

            $limit = match($category) {
                'MEI-GERAL' => 8_100_000,
                'MEI-TAC' => 25_160_000,
            };

            $annualRevenues = [
                [
                    'year' => $this->currentYear,
                    'total' => 1_000_000,
                    'limit' => $limit,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ]
            ];

            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toMatchArray($annualRevenues);
        }
    )->with(['MEI-GERAL', 'MEI-TAC']);

    it('should return an "above" status if the annual revenue limit has been exceeded by up to 20%',
        function(string $category) {
            $income = match($category) {
                'MEI-GERAL' => 9_720_000,
                'MEI-TAC' => 30_192_000,
            };
            $incomeInOpeningYear = match($category) {
                'MEI-GERAL' => 5_670_000,
                'MEI-TAC' => 17_612_002,
            };

            MeiCategory::factory()->for($this->subscriber, 'owner')->create([
                'type' => $category,
                'creation_date' => Carbon::createFromDate(2024, 6, 1)
            ]);
            Report::factory()
                ->for($this->subscriber, 'owner')
                ->create([
                    'trade_with_invoice' => $incomeInOpeningYear,
                    'trade_without_invoice' => 0,
                    'industry_with_invoice' => 0,
                    'industry_without_invoice' => 0,
                    'services_with_invoice' => 0,
                    'services_without_invoice' => 0,
                    'period' => Carbon::createFromDate(2024, 7, 1)
                ]);
            Report::factory()
                ->for($this->subscriber, 'owner')
                ->create([
                    'trade_with_invoice' => $income,
                    'trade_without_invoice' => 0,
                    'industry_with_invoice' => 0,
                    'industry_without_invoice' => 0,
                    'services_with_invoice' => 0,
                    'services_without_invoice' => 0,
                    'period' => Carbon::createFromDate(null, 1, 1)
                ]);

            $limit = match($category) {
                'MEI-GERAL' => 8_100_000,
                'MEI-TAC' => 25_160_000,
            };
            $limitInOpeningYear = match($category) {
                'MEI-GERAL' => 4_725_000,
                'MEI-TAC' => 14_676_669,
            };

            $annualRevenues = [
                [
                    'year' => $this->currentYear,
                    'total' => $income,
                    'limit' => $limit,
                    'limit_exceeded' => true,
                    'status' => 'above'
                ],
                [
                    'year' => 2024,
                    'total' => $incomeInOpeningYear,
                    'limit' => $limitInOpeningYear,
                    'limit_exceeded' => true,
                    'status' => 'above'
                ]
            ];

            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toHaveLength(2)->toMatchArray($annualRevenues);
        }
    )->with(['MEI-GERAL', 'MEI-TAC']);

    it('should return a "beyond" status if the annual revenue limit has been exceeded by more than 20%',
        function(string $category) {
            $income = match($category) {
                'MEI-GERAL' => 9_800_000,
                'MEI-TAC' => 31_000_000,
            };
            $incomeInOpeningYear = match($category) {
                'MEI-GERAL' => 5_700_000,
                'MEI-TAC' => 17_700_000,
            };

            MeiCategory::factory()->for($this->subscriber, 'owner')->create([
                'type' => $category,
                'creation_date' => Carbon::createFromDate(2024, 6, 1)
            ]);
            Report::factory()
                ->for($this->subscriber, 'owner')
                ->create([
                    'trade_with_invoice' => $incomeInOpeningYear,
                    'trade_without_invoice' => 0,
                    'industry_with_invoice' => 0,
                    'industry_without_invoice' => 0,
                    'services_with_invoice' => 0,
                    'services_without_invoice' => 0,
                    'period' => Carbon::createFromDate(2024, 7, 1)
                ]);
            Report::factory()
                ->for($this->subscriber, 'owner')
                ->create([
                    'trade_with_invoice' => $income,
                    'trade_without_invoice' => 0,
                    'industry_with_invoice' => 0,
                    'industry_without_invoice' => 0,
                    'services_with_invoice' => 0,
                    'services_without_invoice' => 0,
                    'period' => Carbon::createFromDate(null, 1, 1)
                ]);

            $limit = match($category) {
                'MEI-GERAL' => 8_100_000,
                'MEI-TAC' => 25_160_000,
            };
            $limitInOpeningYear = match($category) {
                'MEI-GERAL' => 4_725_000,
                'MEI-TAC' => 14_676_669,
            };

            $annualRevenues = [
                [
                    'year' => $this->currentYear,
                    'total' => $income,
                    'limit' => $limit,
                    'limit_exceeded' => true,
                    'status' => 'beyond'
                ],
                [
                    'year' => 2024,
                    'total' => $incomeInOpeningYear,
                    'limit' => $limitInOpeningYear,
                    'limit_exceeded' => true,
                    'status' => 'beyond'
                ],
            ];

            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toHaveLength(2)->toMatchArray($annualRevenues);
        }
    )->with(['MEI-GERAL', 'MEI-TAC']);

    it('should return a greater annual revenue limit in 2022 if the MEI Caminhoneiro opening occurred on or before March 31',
        function(string $date) {
            MeiCategory::factory()->for($this->subscriber, 'owner')
                ->tac()
                ->state(['creation_date' => Carbon::createFromFormat('Y-m-d', $date)])
                ->create();

            $annualRevenues = [
                [
                    'year' => $this->currentYear,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2024,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2023,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2022,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
            ];

            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toHaveLength(4)->toMatchArray($annualRevenues);
        }
    )->with(['2022-01-01', '2022-03-31']);

    it('should return a proportional annual revenue limit in 2022 if the MEI Caminhoneiro opening occurred after March 31',
        function() {
            MeiCategory::factory()->for($this->subscriber, 'owner')
                ->tac()
                ->state(['creation_date' => Carbon::createFromDate(2022, 4, 1)])
                ->create();

            $annualRevenues = [
                [
                    'year' => $this->currentYear,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2024,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2023,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2022,
                    'total' => 0,
                    'limit' => 18_870_003,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
            ];

            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toHaveLength(4)->toMatchArray($annualRevenues);
        }
    );

    it('should return a greater annual revenue limit in 2022 if the MEI changed to MEI Caminhoneiro on or before March 31',
        function() {
            MeiCategory::factory()->for($this->subscriber, 'owner')
                ->count(2)
                ->sequence(
                    ['type' => 'MEI-GERAL', 'creation_date' => Carbon::createFromDate(2021, 1, 1)],
                    ['type' => 'MEI-TAC', 'creation_date' => Carbon::createFromDate(2022, 3, 31)]
                )->create();

            $annualRevenues = [
                [
                    'year' => $this->currentYear,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2024,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2023,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2022,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2021,
                    'total' => 0,
                    'limit' => 8_100_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
            ];

            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toHaveLength(5)->toMatchArray($annualRevenues);
        }
    );

    it('should return a greater annual revenue limit starting in 2023 if the MEI changed to MEI Caminhoneiro on or before March 31, 2022, but removed Table A after March',
        function() {
            MeiCategory::factory()->for($this->subscriber, 'owner')
                ->count(2)
                ->sequence(
                    [
                        'type' => 'MEI-GERAL',
                        'creation_date' => Carbon::createFromDate(2021, 1, 1)
                    ],
                    [
                        'type' => 'MEI-TAC',
                        'creation_date' => Carbon::createFromDate(2022, 3, 31),
                        'table_a_excluded_after_032022' => true
                    ]
                )->create();

            $annualRevenues = [
                [
                    'year' => $this->currentYear,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2024,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2023,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2022,
                    'total' => 0,
                    'limit' => 8_100_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2021,
                    'total' => 0,
                    'limit' => 8_100_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
            ];

            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toHaveLength(5)->toMatchArray($annualRevenues);
        }
    );

    it('should return a greater annual revenue limit starting in 2023 if the MEI in 2022 changed to MEI Caminhoneiro after March 31',
        function() {
            MeiCategory::factory()->for($this->subscriber, 'owner')
                ->count(2)
                ->sequence(
                    [
                        'type' => 'MEI-GERAL',
                        'creation_date' => Carbon::createFromDate(2021, 1, 1)
                    ],
                    [
                        'type' => 'MEI-TAC',
                        'creation_date' => Carbon::createFromDate(2022, 4, 1),
                        'table_a_excluded_after_032022' => true
                    ]
                )->create();

            $annualRevenues = [
                [
                    'year' => $this->currentYear,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2024,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2023,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2022,
                    'total' => 0,
                    'limit' => 8_100_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2021,
                    'total' => 0,
                    'limit' => 8_100_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
            ];

            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toHaveLength(5)->toMatchArray($annualRevenues);
        }
    );

    it('should return the new annual revenue limit starting in the following year if the MEI changed categories within the same year',
        function() {
            MeiCategory::factory()->for($this->subscriber, 'owner')
                ->count(2)
                ->sequence(
                    ['type' => 'MEI-TAC', 'creation_date' => Carbon::createFromDate(2024, 1, 31)],
                    ['type' => 'MEI-GERAL', 'creation_date' => Carbon::createFromDate(2024, 6, 1)],
                )->create();

            $annualRevenues = [
                [
                    'year' => $this->currentYear,
                    'total' => 0,
                    'limit' => 8_100_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2024,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
            ];

            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toHaveLength(2)->toMatchArray($annualRevenues);
        }
    );

    it('should return a proportional annual revenue limit for the transition year if the MEI changed categories in different years',
        function() {
            MeiCategory::factory()->for($this->subscriber, 'owner')
                ->count(3)
                ->sequence(
                    ['type' => 'MEI-GERAL', 'creation_date' => Carbon::createFromDate(2023, 6, 1)],
                    ['type' => 'MEI-TAC', 'creation_date' => Carbon::createFromDate(2024, 1, 31)],
                    ['type' => 'MEI-GERAL', 'creation_date' => Carbon::createFromDate(2025, 6, 1)],
                )->create();

            $annualRevenues = [
                [
                    'year' => $this->currentYear,
                    'total' => 0,
                    'limit' => 15_208_335,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2024,
                    'total' => 0,
                    'limit' => 25_160_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
                [
                    'year' => 2023,
                    'total' => 0,
                    'limit' => 4_725_000,
                    'limit_exceeded' => false,
                    'status' => 'below'
                ],
            ];

            $event = new AnnualRevenueChanged;
            $payload = $event->broadcastWith();

            expect($payload)->toMatchArray($annualRevenues);
        }
    );
});
