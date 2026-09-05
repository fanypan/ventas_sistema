@php
    use App\Helpers\SettingHelper;
    use App\Services\Media\MediaUrl;

    $brandLogo = app(MediaUrl::class)->settingDataUri(SettingHelper::getValue('app_logo'));
    $brandName = SettingHelper::getValue('company_name') ?: SettingHelper::getValue('app_name');
@endphp

@if ($brandLogo || $brandName)
<table style="width:100%; margin-bottom:10px; border-collapse:collapse; border-bottom:2px solid #4f46e5; padding-bottom:8px;">
    <tr>
        @if ($brandLogo)
            <td style="width:58px; vertical-align:middle; padding:0 10px 8px 0;">
                <img src="{{ $brandLogo }}" alt="" style="max-height:40px; max-width:52px;">
            </td>
        @endif
        @if ($brandName)
            <td style="vertical-align:middle; padding-bottom:8px;">
                <strong style="font-size:12px; color:#0f172a;">{{ $brandName }}</strong>
            </td>
        @endif
    </tr>
</table>
@endif
