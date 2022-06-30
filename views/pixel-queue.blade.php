<!-- Pixels notifier queue script -->
<style>
.notifier-pixel{
    display:none!important;
    opacity:0!important;
    width:0!important;
    height:0!important
}
</style>
<script>
var notifier = {
    pixel: {
        tried(id){
            fetch('@route('notifier.pixel.tried')/' + id)
                .then(response => response.json())
                .then(data => console.log(data));
        },
        unqueue(id){
            fetch('@route('notifier.pixel.unqueue')/' + id)
                .then(response => response.json())
                .then(data => console.log(data));
        }
    }
}
</script>

@foreach($pixels as $id => $pixel)
<img src="{{ $pixel }}" class="notifier-pixel" onload="notifier.pixel.unqueue('{{ $id }}')" onerror="notifier.pixel.tried('{{ $id }}')" />
@endforeach
