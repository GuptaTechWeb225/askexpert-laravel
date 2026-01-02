<html>
<table>
    <thead>
    <tr>
        <th style="font-size:18px">{{ translate('restaurant_List') }}</th>
    </tr>
    <tr>
        <th>{{ translate('restaurant_Analytics') }}</th>
        <th></th>
        <th>
            {{ translate('total_Restaurants').' - '.count($data['restaurants']) }}
        </th>
    </tr>
    <tr>
        <th>{{translate('Filter_Criteria')}} -</th>
        <th></th>
        <th>
            {{ translate('status') . ' - ' . ($data['status'] === '1' ? 'Active' : ($data['status'] === '0' ? 'Inactive' : 'All')) }}
            <br>
            {{ translate('sort_by') . ' - ' . (!empty($data['sortBy']) ? $data['sortBy'] : 'N/A') }}
            <br>
            {{ translate('restaurant_plan'). ' - ' . (!empty($data['restaurantPlan']) ? $data['restaurantPlan'] : 'N/A') }}
            <br>
            {{ translate('choose_first'). ' - ' . (!empty($data['chooseFirst']) ? $data['chooseFirst'] : 'N/A') }}
            <br>
            {{ translate('search_Bar_Content'). ' - ' . (!empty($data['searchValue']) ? $data['searchValue'] : 'N/A') }}
            <br>
            {{translate('joining_start_date').' - '. (!empty($data['joiningStartDate']) ?  $data['joiningStartDate']->format('d F Y') : 'N/A') }}
            <br>
            {{translate('joining_end_date').' - '. (!empty($data['joiningEndDate']) ?  $data['joiningEndDate']->format('d F Y') : 'N/A') }}
        </th>
    </tr>
    <tr>
        <td>{{ translate('SL') }}</td>
        <td>{{ translate('restaurant_Image') }}</td>
        <td>{{ translate('Name') }}</td>
        <td>{{ translate('email') }}</td>
        <td>{{ translate('phone') }}</td>
        <td>{{ translate('city') }}</td>
        <td>{{ translate('plan_type') }}</td>
        <td>{{ translate('boost') }}</td>
        <td>{{ translate('date_of_Joining') }}</td>
        <td>{{ translate('status') }}</td>
        <td>{{ translate('total_Reviews') }}</td>
    </tr>
    </thead>
    <tbody>
    @foreach ($data['restaurants'] as $key=>$item)
        <tr>
            <td>{{ ++$key }}</td>
            <td style="height:80px"></td>
            <td>{{ ucwords($item->restaurant_name ?? translate('not_found')) }}</td>
            <td>{{ $item->email ?? translate('not_found') }}</td>
            <td>{{ $item->phone ?? translate('not_found') }}</td>
            <td>{{ $item->city  ?? translate('not_found') }}</td>
            <td>{{ $item->plan_name ?? 'free'}}</td>
            <td>{{ translate($item->boost == 1 ? 'active' : 'inactive') }}</td>
            <td>{{ date('d M, Y', strtotime($item->created_at)) }}</td>
            <td>{{ translate($item->is_active == 1 ? 'active' : 'inactive') }}</td>
            <td>{{ $item->reviews->count() ?? 0 }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</html>
