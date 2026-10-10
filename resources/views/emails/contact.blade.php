<x-mail::message>
# Nova poruka sa sajta

**Ime:** {{ $data['name'] }}
**Mejl:** {{ $data['email'] }}
**Telefon:** {{ $data['phone'] ?? '—' }}

{{ $data['message'] }}
</x-mail::message>
