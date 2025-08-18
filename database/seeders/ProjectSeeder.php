<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectResource;
use App\Models\ProjectCost;
use App\Models\ProjectMilestone;
use App\Models\ProjectTeam;
use App\Models\User;
use App\Models\Customer;
use App\Models\Company;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get company first
        $company = Company::first();

        // Get or create sample users
        $user1 = User::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'name' => 'John Doe',
                'workos_id' => 'user_123',
                'avatar' => 'https://ui-avatars.com/api/?name=John+Doe',
                'company_id' => $company->id,
            ]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'jane.smith@example.com'],
            [
                'name' => 'Jane Smith',
                'workos_id' => 'user_124',
                'avatar' => 'https://ui-avatars.com/api/?name=Jane+Smith',
                'company_id' => $company->id,
            ]
        );

        // Get or create sample customers
        $customer1 = Customer::firstOrCreate(
            ['email' => 'client1@example.com'],
            [
                'company_id' => $company->id,
                'code' => 'CUST001',
                'name' => 'ABC Corporation',
                'phone' => '+1234567890',
                'address' => '123 Business St, City, Country',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'country' => 'Indonesia',
                'postal_code' => '12345',
                'customer_type' => 'individual',
                'status' => 'active',
            ]
        );

        $customer2 = Customer::firstOrCreate(
            ['email' => 'client2@example.com'],
            [
                'company_id' => $company->id,
                'code' => 'CUST002',
                'name' => 'XYZ Industries',
                'phone' => '+0987654321',
                'address' => '456 Industry Ave, City, Country',
                'city' => 'Bandung',
                'state' => 'West Java',
                'country' => 'Indonesia',
                'postal_code' => '54321',
                'customer_type' => 'individual',
                'status' => 'active',
            ]
        );

        // Create sample projects
        $project1 = Project::create([
            'name' => 'E-commerce Platform Development',
            'code' => 'PRJ202508001',
            'description' => 'Develop a modern e-commerce platform with advanced features including payment integration, inventory management, and analytics dashboard.',
            'status' => 'active',
            'priority' => 'high',
            'start_date' => '2025-01-15',
            'end_date' => '2025-06-30',
            'actual_start_date' => '2025-01-15',
            'budget' => 50000000,
            'actual_cost' => 25000000,
            'progress_percentage' => 65,
            'project_manager_id' => $user1->id,
            'client_id' => $customer1->id,
            'company_id' => $company->id,
            'location' => 'Jakarta, Indonesia',
            'contact_person' => 'Sarah Johnson',
            'contact_email' => 'sarah.johnson@abccorp.com',
            'contact_phone' => '+628123456789',
        ]);

        $project2 = Project::create([
            'name' => 'Mobile App Development',
            'code' => 'PRJ202508002',
            'description' => 'Create a cross-platform mobile application for iOS and Android with real-time features and offline capabilities.',
            'status' => 'planning',
            'priority' => 'medium',
            'start_date' => '2025-03-01',
            'end_date' => '2025-08-31',
            'budget' => 35000000,
            'actual_cost' => 0,
            'progress_percentage' => 0,
            'project_manager_id' => $user2->id,
            'client_id' => $customer2->id,
            'company_id' => $company->id,
            'location' => 'Bandung, Indonesia',
            'contact_person' => 'Michael Chen',
            'contact_email' => 'michael.chen@xyzinc.com',
            'contact_phone' => '+628987654321',
        ]);

        $project3 = Project::create([
            'name' => 'ERP System Implementation',
            'code' => 'PRJ202508003',
            'description' => 'Implement and customize an Enterprise Resource Planning system for manufacturing operations.',
            'status' => 'completed',
            'priority' => 'urgent',
            'start_date' => '2024-09-01',
            'end_date' => '2025-02-28',
            'actual_start_date' => '2024-09-01',
            'actual_end_date' => '2025-02-28',
            'budget' => 75000000,
            'actual_cost' => 72000000,
            'progress_percentage' => 100,
            'project_manager_id' => $user1->id,
            'client_id' => $customer1->id,
            'company_id' => $company->id,
            'location' => 'Surabaya, Indonesia',
            'contact_person' => 'David Wilson',
            'contact_email' => 'david.wilson@abccorp.com',
            'contact_phone' => '+628112233445',
        ]);

        // Create sample tasks for project 1
        $task1 = ProjectTask::create([
            'name' => 'Database Design',
            'description' => 'Design and implement the database schema for the e-commerce platform',
            'status' => 'completed',
            'priority' => 'high',
            'type' => 'feature',
            'project_id' => $project1->id,
            'assigned_to' => $user1->id,
            'created_by' => $user1->id,
            'start_date' => '2025-01-15',
            'due_date' => '2025-02-15',
            'actual_start_date' => '2025-01-15',
            'actual_end_date' => '2025-02-10',
            'estimated_hours' => 80,
            'actual_hours' => 75,
            'progress_percentage' => 100,
        ]);

        $task2 = ProjectTask::create([
            'name' => 'Frontend Development',
            'description' => 'Develop the user interface using React.js with responsive design',
            'status' => 'in_progress',
            'priority' => 'high',
            'type' => 'feature',
            'project_id' => $project1->id,
            'assigned_to' => $user2->id,
            'created_by' => $user1->id,
            'start_date' => '2025-02-01',
            'due_date' => '2025-04-30',
            'actual_start_date' => '2025-02-01',
            'estimated_hours' => 160,
            'actual_hours' => 100,
            'progress_percentage' => 65,
        ]);

        $task3 = ProjectTask::create([
            'name' => 'Payment Integration',
            'description' => 'Integrate payment gateways (Midtrans, Xendit) for secure transactions',
            'status' => 'todo',
            'priority' => 'urgent',
            'type' => 'feature',
            'project_id' => $project1->id,
            'assigned_to' => $user1->id,
            'created_by' => $user1->id,
            'start_date' => '2025-04-01',
            'due_date' => '2025-05-15',
            'estimated_hours' => 120,
            'actual_hours' => 0,
            'progress_percentage' => 0,
        ]);

        // Create sample resources
        ProjectResource::create([
            'project_id' => $project1->id,
            'user_id' => $user1->id,
            'resource_type' => 'human',
            'resource_name' => 'John Doe',
            'description' => 'Senior Full Stack Developer',
            'role' => 'developer',
            'hourly_rate' => 150000,
            'allocated_hours' => 320,
            'actual_hours' => 200,
            'start_date' => '2025-01-15',
            'end_date' => '2025-06-30',
            'availability' => 'full_time',
            'status' => 'allocated',
            'utilization_percentage' => 85,
        ]);

        ProjectResource::create([
            'project_id' => $project1->id,
            'user_id' => $user2->id,
            'resource_type' => 'human',
            'resource_name' => 'Jane Smith',
            'description' => 'Frontend Developer',
            'role' => 'developer',
            'hourly_rate' => 120000,
            'allocated_hours' => 280,
            'actual_hours' => 150,
            'start_date' => '2025-02-01',
            'end_date' => '2025-06-30',
            'availability' => 'full_time',
            'status' => 'allocated',
            'utilization_percentage' => 75,
        ]);

        // Create sample costs
        ProjectCost::create([
            'project_id' => $project1->id,
            'cost_name' => 'Development Tools License',
            'description' => 'Annual license for development tools and software',
            'cost_type' => 'software',
            'cost_category' => 'direct',
            'estimated_cost' => 5000000,
            'actual_cost' => 4800000,
            'budgeted_cost' => 5000000,
            'incurred_date' => '2025-01-20',
            'status' => 'approved',
            'approved_by' => $user1->id,
            'approved_date' => '2025-01-15',
            'vendor' => 'JetBrains',
            'notes' => 'Annual subscription for development tools',
        ]);

        ProjectCost::create([
            'project_id' => $project1->id,
            'cost_name' => 'Server Infrastructure',
            'description' => 'Cloud server costs for development and testing',
            'cost_type' => 'equipment',
            'cost_category' => 'direct',
            'estimated_cost' => 8000000,
            'actual_cost' => 7500000,
            'budgeted_cost' => 8000000,
            'incurred_date' => '2025-02-01',
            'status' => 'approved',
            'approved_by' => $user1->id,
            'approved_date' => '2025-01-25',
            'vendor' => 'AWS',
            'notes' => 'Monthly cloud infrastructure costs',
        ]);

        // Create sample milestones
        ProjectMilestone::create([
            'project_id' => $project1->id,
            'name' => 'Database Design Complete',
            'description' => 'Database schema design and implementation completed',
            'status' => 'completed',
            'planned_date' => '2025-02-15',
            'actual_date' => '2025-02-10',
            'progress_percentage' => 100,
            'priority' => 'high',
            'responsible_person' => $user1->id,
        ]);

        ProjectMilestone::create([
            'project_id' => $project1->id,
            'name' => 'Frontend MVP Ready',
            'description' => 'Minimum viable product frontend ready for testing',
            'status' => 'in_progress',
            'planned_date' => '2025-04-30',
            'progress_percentage' => 65,
            'priority' => 'high',
            'responsible_person' => $user2->id,
        ]);

        ProjectMilestone::create([
            'project_id' => $project1->id,
            'name' => 'Payment Integration Complete',
            'description' => 'Payment gateway integration completed and tested',
            'status' => 'planned',
            'planned_date' => '2025-05-15',
            'progress_percentage' => 0,
            'priority' => 'critical',
            'responsible_person' => $user1->id,
        ]);

        // Create sample team members
        ProjectTeam::create([
            'project_id' => $project1->id,
            'user_id' => $user1->id,
            'role' => 'project_manager',
            'status' => 'active',
            'joined_date' => '2025-01-15',
            'allocation_percentage' => 100,
            'hourly_rate' => 150000,
        ]);

        ProjectTeam::create([
            'project_id' => $project1->id,
            'user_id' => $user2->id,
            'role' => 'developer',
            'status' => 'active',
            'joined_date' => '2025-02-01',
            'allocation_percentage' => 100,
            'hourly_rate' => 120000,
        ]);

        $this->command->info('Project Management sample data seeded successfully!');
    }
}
