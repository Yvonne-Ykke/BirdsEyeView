<div>
    <table class="min-w-full divide-y divide-gray-300">
        <thead>
        <tr>
            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Naam</th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Gemiddelde recensie</th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Aantal gemaakte titels</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
        @foreach($data as $row)
            <tr>
                <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">{{ $row['name'] }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $row['made_titles'] }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $row['true_average_rating'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
