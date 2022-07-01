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
        request(url){
            fetch(url)
                .then(response => {
                    try {
                        return response.json();
                    } catch (e) {
                        return {
                            success: false,
                            message: e.message,
                        };
                    }
                }).then(data => console.log('%c🏓 Notifier: %c' + data.message, 'color:#7289DA', 'color:' + (data.success ? 'green' : 'red')));
        },
        tried(id){
            this.request('@route('notifier.pixel.tried')/' + id);
        },
        unqueue(id){
            this.request('@route('notifier.pixel.unqueue')/' + id);
        }
    }
}
</script>

@foreach($pixels as $id => $pixel)
<img src="{{ $pixel }}" class="notifier-pixel" onload="notifier.pixel.unqueue('{{ $id }}')" onerror="notifier.pixel.tried('{{ $id }}')" />
@endforeach
