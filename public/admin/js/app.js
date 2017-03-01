$(document).foundation()

$('.tmdb-link').click(function(e){
    var id = $('[name="tmdb_id"]').val();
    if(id == ''){
        alert('Введите TMDB id');
    }else{

        if(type_ == 'film'){
            $.get('/manage/getonefilm/'+id,null,function(data){
                data = JSON.parse(data);
                console.log(data);
                data.cats.forEach(function(cat){
                    $('[data-tmdb="'+cat.tmdb_id+'"]').prop('checked',true);
                    //$('[name="image"]').val(data.image);
                    //$('[name="image"]').val(data.image);
                });
                $('[name="stars"]').val(data.stars);
                $('[name="image"]').val(data.image);
                $('[name="title"]').val(data.title);
                $('[name="title_en"]').val(data.title_en);
                $('[name="image_back"]').val(data.image_back);
                $('[name="description"]').val(data.description);
                $('[name="release_date"]').val(data.release_date);
            });
        }else
        if(type_ == 'serial'){
            $.get('/manage/getoneser/'+id,null,function(data){
                data = JSON.parse(data);
                console.log(data);
                data.cats.forEach(function(cat){
                    $('[data-tmdb="'+cat.tmdb_id+'"]').prop('checked',true);
                    //$('[name="image"]').val(data.image);
                    //$('[name="image"]').val(data.image);
                });
                $('[name="stars"]').val(data.stars);
                $('[name="image"]').val(data.image);
                $('[name="title"]').val(data.title);
                $('[name="title_en"]').val(data.title_en);
                $('[name="image_back"]').val(data.image_back);
                $('[name="description"]').val(data.description);
                $('[name="year"]').val(data.year);
            });
        }
        $('.gen-label').click();
    }

    e.preventDefault();
})

function slugify(text)
{
    return text.toString().toLowerCase()
        .replace(/\s+/g, '-')           // Replace spaces with -
        .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
        .replace(/\-\-+/g, '-')         // Replace multiple - with single -
        .replace(/^-+/, '')             // Trim - from start of text
        .replace(/-+$/, '');            // Trim - from end of text
}

$('.gen-label').click(function(e){
    if($('[name="title_en"]').length > 0 && $('[name="title_en"]').val()!==''){
        var text = $('[name="title_en"]').val();
    }else{
        var text = $('[name="title"]').val();
    }
    $('[name="label"]').val(slugify(text));

    e.preventDefault();
})