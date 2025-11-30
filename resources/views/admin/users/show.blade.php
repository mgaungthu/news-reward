@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">

    {{-- Back --}}
    <a href="{{ route('users.index') }}" 
       class="inline-block mb-4 text-blue-600 hover:underline">
        ← Back to User List
    </a>

    {{-- User Overview --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">User Information</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Created At:</strong> {{ $user->created_at->format('M d, Y H:i') }}</p>
                <p><strong>Updated At:</strong> {{ $user->updated_at->format('M d, Y H:i') }}</p>
            </div>

            <div>
                <p><strong>Current Points:</strong> 
                    <span class="text-green-600 font-bold">{{ $currentPoints }}</span>
                </p>

                <p><strong>Total Referral Rewarded:</strong> 
                    <span class="text-blue-600 font-bold">{{ $user->referral_rewarded }}</span>
                </p>

                <p><strong>Referral Code:</strong> {{ $user->referral_code }}</p>
                <p><strong>Referred By:</strong> {{ $user->referred_by ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- Point Records --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Point Records</h2>

        @if($pointRecords->count())
        <table class="min-w-full border border-gray-300 rounded-lg overflow-hidden shadow-sm">
            <thead class="bg-gray-200 text-gray-700 uppercase text-sm">
                <tr>
                    <th class="py-3 px-4 border">Points Change</th>
                    <th class="py-3 px-4 border">Source</th>
                    <th class="py-3 px-4 border">Date</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                @foreach($pointRecords as $record)
                <tr class="hover:bg-gray-50 transition">
                    <td class="py-3 px-4 border">{{ $record->points_change }}</td>
                    <td class="py-3 px-4 border capitalize">{{ $record->source }}</td>
                    <td class="py-3 px-4 border">{{ $record->created_at->format('M d, Y H:i:s') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $pointRecords->links() }}
        </div>
        @else
            <p class="text-gray-500">No point records found.</p>
        @endif
    </div>

    {{-- Referral List --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Referral List</h2>

        @if($referralList->count())
        <table class="min-w-full border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-3 border">Name</th>
                    <th class="py-2 px-3 border">Email</th>
                    <th class="py-2 px-3 border">Registered</th>
                </tr>
            </thead>
            <tbody>
                @foreach($referralList as $ref)
                <tr>
                    <td class="py-2 px-3 border">{{ $ref->name }}</td>
                    <td class="py-2 px-3 border">{{ $ref->email }}</td>
                    <td class="py-2 px-3 border">{{ $ref->created_at->format('M d, Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p class="text-gray-500">No referrals found.</p>
        @endif
    </div>

</div>
@endsection