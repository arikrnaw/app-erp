<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prospect;
use App\Models\FollowUp;
use App\Models\SupportTicket;
use App\Models\CustomerSegment;
use App\Models\Customer;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;

class CrmSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first company and users
        $company = Company::first();
        if (!$company) {
            $this->command->error('No company found. Please run CompanySeeder first.');
            return;
        }

        $users = User::where('company_id', $company->id)->get();
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        $customers = Customer::where('company_id', $company->id)->get();
        if ($customers->isEmpty()) {
            $this->command->error('No customers found. Please run CustomerSeeder first.');
            return;
        }

        // Create Customer Segments
        $this->createCustomerSegments($company, $users->first());

        // Create Prospects
        $this->createProspects($company, $users);

        // Create Follow-ups
        $this->createFollowUps($company, $users, $customers);

        // Create Support Tickets
        $this->createSupportTickets($company, $users, $customers);
    }

    private function createCustomerSegments(Company $company, User $user): void
    {
        $segments = [
            [
                'name' => 'VIP Customers',
                'description' => 'High-value customers with premium status',
                'criteria' => ['status' => 'active', 'total_spent' => ['operator' => 'gte', 'value' => 10000]],
                'color' => '#10b981',
                'is_active' => true,
            ],
            [
                'name' => 'New Customers',
                'description' => 'Recently acquired customers',
                'criteria' => ['created_at' => ['operator' => 'gte', 'value' => '30 days ago']],
                'color' => '#3b82f6',
                'is_active' => true,
            ],
            [
                'name' => 'At Risk',
                'description' => 'Customers who haven\'t made a purchase recently',
                'criteria' => ['last_purchase' => ['operator' => 'lt', 'value' => '90 days ago']],
                'color' => '#ef4444',
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Large enterprise customers',
                'criteria' => ['company_size' => 'enterprise'],
                'color' => '#8b5cf6',
                'is_active' => true,
            ],
            [
                'name' => 'SMB',
                'description' => 'Small and medium business customers',
                'criteria' => ['company_size' => ['operator' => 'in', 'value' => ['small', 'medium']]],
                'color' => '#f59e0b',
                'is_active' => true,
            ],
        ];

        foreach ($segments as $segmentData) {
            CustomerSegment::create([
                'company_id' => $company->id,
                'created_by' => $user->id,
                ...$segmentData,
            ]);
        }

        $this->command->info('Customer segments created successfully.');
    }

    private function createProspects(Company $company, $users): void
    {
        $sources = ['website', 'referral', 'cold_call', 'social_media', 'email_campaign'];
        $industries = ['Technology', 'Healthcare', 'Finance', 'Education', 'Manufacturing', 'Retail'];
        $statuses = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        $prospects = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@techcorp.com',
                'phone' => '+1-555-0123',
                'company_name' => 'TechCorp Solutions',
                'position' => 'CTO',
                'industry' => 'Technology',
                'source' => 'website',
                'status' => 'qualified',
                'priority' => 'high',
                'estimated_value' => 50000,
                'notes' => 'Interested in enterprise solution. Follow up scheduled.',
                'assigned_to' => $users->random()->id,
                'next_follow_up_date' => Carbon::now()->addDays(3),
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.j@healthcareplus.com',
                'phone' => '+1-555-0124',
                'company_name' => 'Healthcare Plus',
                'position' => 'IT Director',
                'industry' => 'Healthcare',
                'source' => 'referral',
                'status' => 'proposal',
                'priority' => 'urgent',
                'estimated_value' => 75000,
                'notes' => 'Proposal sent. Waiting for response.',
                'assigned_to' => $users->random()->id,
                'next_follow_up_date' => Carbon::now()->addDays(1),
            ],
            [
                'name' => 'Mike Davis',
                'email' => 'mike.davis@financepro.com',
                'phone' => '+1-555-0125',
                'company_name' => 'Finance Pro',
                'position' => 'Operations Manager',
                'industry' => 'Finance',
                'source' => 'cold_call',
                'status' => 'contacted',
                'priority' => 'medium',
                'estimated_value' => 25000,
                'notes' => 'Initial contact made. Interested in demo.',
                'assigned_to' => $users->random()->id,
                'next_follow_up_date' => Carbon::now()->addDays(5),
            ],
            [
                'name' => 'Lisa Chen',
                'email' => 'lisa.chen@edutech.com',
                'phone' => '+1-555-0126',
                'company_name' => 'EduTech Institute',
                'position' => 'Technology Coordinator',
                'industry' => 'Education',
                'source' => 'social_media',
                'status' => 'new',
                'priority' => 'low',
                'estimated_value' => 15000,
                'notes' => 'New lead from LinkedIn campaign.',
                'assigned_to' => $users->random()->id,
                'next_follow_up_date' => Carbon::now()->addDays(7),
            ],
            [
                'name' => 'Robert Wilson',
                'email' => 'robert.w@manufacturingco.com',
                'phone' => '+1-555-0127',
                'company_name' => 'Manufacturing Co',
                'position' => 'Plant Manager',
                'industry' => 'Manufacturing',
                'source' => 'email_campaign',
                'status' => 'negotiation',
                'priority' => 'high',
                'estimated_value' => 100000,
                'notes' => 'Final negotiation phase. Close to deal.',
                'assigned_to' => $users->random()->id,
                'next_follow_up_date' => Carbon::now()->addDays(2),
            ],
        ];

        foreach ($prospects as $prospectData) {
            Prospect::create([
                'company_id' => $company->id,
                ...$prospectData,
            ]);
        }

        $this->command->info('Prospects created successfully.');
    }

    private function createFollowUps(Company $company, $users, $customers): void
    {
        $types = ['call', 'email', 'meeting', 'presentation', 'demo', 'proposal'];
        $methods = ['phone', 'email', 'in_person', 'video_call'];
        $statuses = ['scheduled', 'in_progress', 'completed', 'cancelled'];
        $outcomes = ['positive', 'neutral', 'negative', 'no_response'];

        $followUps = [
            [
                'prospect_id' => Prospect::where('company_id', $company->id)->first()?->id,
                'customer_id' => null,
                'type' => 'meeting',
                'method' => 'video_call',
                'subject' => 'Product Demo for TechCorp',
                'description' => 'Demonstrate our enterprise solution features',
                'status' => 'scheduled',
                'scheduled_date' => Carbon::now()->addDays(2)->setTime(10, 0),
                'assigned_to' => $users->random()->id,
                'notes' => 'Prepare demo environment and case studies.',
            ],
            [
                'prospect_id' => null,
                'customer_id' => $customers->random()->id,
                'type' => 'call',
                'method' => 'phone',
                'subject' => 'Follow-up on Recent Purchase',
                'description' => 'Check customer satisfaction and offer support',
                'status' => 'completed',
                'scheduled_date' => Carbon::now()->subDays(1)->setTime(14, 0),
                'completed_date' => Carbon::now()->subDays(1)->setTime(14, 30),
                'assigned_to' => $users->random()->id,
                'outcome' => 'positive',
                'notes' => 'Customer is very satisfied with the product.',
                'next_action' => 'Send thank you email and offer training session.',
            ],
            [
                'prospect_id' => Prospect::where('company_id', $company->id)->skip(1)->first()?->id,
                'customer_id' => null,
                'type' => 'proposal',
                'method' => 'email',
                'subject' => 'Proposal for Healthcare Plus',
                'description' => 'Send detailed proposal with pricing and timeline',
                'status' => 'completed',
                'scheduled_date' => Carbon::now()->subDays(2)->setTime(9, 0),
                'completed_date' => Carbon::now()->subDays(2)->setTime(9, 15),
                'assigned_to' => $users->random()->id,
                'outcome' => 'positive',
                'notes' => 'Proposal sent successfully. Customer requested additional information.',
                'next_action' => 'Prepare additional technical specifications.',
            ],
            [
                'prospect_id' => null,
                'customer_id' => $customers->random()->id,
                'type' => 'presentation',
                'method' => 'in_person',
                'subject' => 'New Feature Presentation',
                'description' => 'Present upcoming features and get feedback',
                'status' => 'scheduled',
                'scheduled_date' => Carbon::now()->addDays(5)->setTime(15, 0),
                'assigned_to' => $users->random()->id,
                'notes' => 'Prepare presentation slides and demo data.',
            ],
            [
                'prospect_id' => Prospect::where('company_id', $company->id)->skip(2)->first()?->id,
                'customer_id' => null,
                'type' => 'demo',
                'method' => 'video_call',
                'subject' => 'Product Demo for Finance Pro',
                'description' => 'Showcase financial reporting features',
                'status' => 'scheduled',
                'scheduled_date' => Carbon::now()->addDays(4)->setTime(11, 0),
                'assigned_to' => $users->random()->id,
                'notes' => 'Prepare financial demo data and use cases.',
            ],
        ];

        foreach ($followUps as $followUpData) {
            FollowUp::create([
                'company_id' => $company->id,
                ...$followUpData,
            ]);
        }

        $this->command->info('Follow-ups created successfully.');
    }

    private function createSupportTickets(Company $company, $users, $customers): void
    {
        $categories = ['Technical', 'Billing', 'General', 'Feature Request', 'Bug Report'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['open', 'in_progress', 'waiting_for_customer', 'resolved', 'closed'];

        $tickets = [
            [
                'customer_id' => $customers->random()->id,
                'subject' => 'Login Issue - Cannot Access Dashboard',
                'description' => 'I am unable to log into my account. Getting an error message.',
                'priority' => 'high',
                'status' => 'in_progress',
                'category' => 'Technical',
                'assigned_to' => $users->random()->id,
                'due_date' => Carbon::now()->addDays(1),
                'estimated_resolution_time' => 120,
            ],
            [
                'customer_id' => $customers->random()->id,
                'subject' => 'Billing Question - Invoice Discrepancy',
                'description' => 'There seems to be an error in my latest invoice. Please review.',
                'priority' => 'medium',
                'status' => 'open',
                'category' => 'Billing',
                'assigned_to' => $users->random()->id,
                'due_date' => Carbon::now()->addDays(3),
                'estimated_resolution_time' => 60,
            ],
            [
                'customer_id' => $customers->random()->id,
                'subject' => 'Feature Request - Mobile App',
                'description' => 'Would like to request a mobile app version of the platform.',
                'priority' => 'low',
                'status' => 'open',
                'category' => 'Feature Request',
                'assigned_to' => $users->random()->id,
                'due_date' => Carbon::now()->addDays(7),
                'estimated_resolution_time' => 30,
            ],
            [
                'customer_id' => $customers->random()->id,
                'subject' => 'Bug Report - Export Function Not Working',
                'description' => 'The export to Excel function is not working properly.',
                'priority' => 'urgent',
                'status' => 'resolved',
                'category' => 'Bug Report',
                'assigned_to' => $users->random()->id,
                'resolved_at' => Carbon::now()->subDays(1),
                'actual_resolution_time' => 90,
                'customer_satisfaction_rating' => 5,
                'internal_notes' => 'Fixed export function. Issue was with file permissions.',
            ],
            [
                'customer_id' => $customers->random()->id,
                'subject' => 'General Question - API Documentation',
                'description' => 'Need help understanding the API documentation.',
                'priority' => 'medium',
                'status' => 'waiting_for_customer',
                'category' => 'General',
                'assigned_to' => $users->random()->id,
                'due_date' => Carbon::now()->addDays(2),
                'estimated_resolution_time' => 45,
            ],
        ];

        foreach ($tickets as $ticketData) {
            $ticket = SupportTicket::create([
                'company_id' => $company->id,
                'created_by' => $users->random()->id,
                ...$ticketData,
            ]);

            // Generate ticket number
            $ticket->update([
                'ticket_number' => 'TKT-' . date('Y') . '-' . str_pad($ticket->id, 4, '0', STR_PAD_LEFT)
            ]);
        }

        $this->command->info('Support tickets created successfully.');
    }
}
