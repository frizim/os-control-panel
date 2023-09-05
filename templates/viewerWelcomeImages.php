<!DOCTYPE html>
<html lang='en'>
    <head>
        <title><?= $v['grid-name'] ?></title>
        <style>
            body {
                background-color: rgb(0, 0, 0);
                width: 100%;
                height: 100%;
                overflow: hidden;
                font-family: 'Arial', sans-serif;
                font-size: 11px;
                padding: 0;
                margin: 0;
            }

            .InfoBox {
                width: 300px;
                height: auto;
                background: rgba(0, 0, 0, 0.85);
                color: rgb(220, 220, 220);
                padding: 10px;
            }

            .InfoBox a {
                color: rgb(220, 220, 220);
                text-decoration: underline;
            }

            .InfoBox a:hover {
                color: rgb(255, 255, 255);
                text-decoration: underline;
            }

            .InfoBoxTitle {
                width: 100%;
                height: auto;
                padding: 0;
                padding-bottom: 5px;
                margin-bottom: 5px;
                border: 0px dashed rgb(128, 128, 128);
                border-bottom-width: 1px;
                color: rgb(220, 220, 220);
                font-weight: bold;
                font-size: 14px;
            }

            .GridLogo {
                position: absolute;
                top: 50px;
                left: 50px;
                border: 0;
            }

            .ScrollBar::-webkit-scrollbar {
                width: 3px;
            }

            .ScrollBar::-webkit-scrollbar-track {
                -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
            }

            .ScrollBar::-webkit-scrollbar-thumb {
                background: rgba(38, 38, 38, 0.9);
                -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.5);
            }
        </style>
    </head>
    <body>
        <img src='<?= $v['image-1'] ?>' style='border: 0; display: block; opacity: 0; position: absolute;' id='Image1' alt=''/>
        <img src='<?= $v['image-2'] ?>' style='border: 0; display: block; opacity: 0; position: absolute;' id='Image2' alt=''/>
        <script type='text/javascript'>
            var Images = <?= $v['json-image-array'] ?>;
            var MakeAnimation = true;
            var CurrentIndex = 2;
            var CurrentImage = -1;
            var ImageTimeout = 0;
            var CurrentImageTranslation = -1;
            var ImagePositions = [{'x': -50, 'y': -50, 'px': 1, 'py': 1},
                                  {'x': -50, 'y': -50, 'px': 1, 'py': 1}];
            document.getElementById('Image1').onload = function() {
                                                            if (MakeAnimation)
                                                            {
                                                                this.style.width = (window.innerWidth + 100) + 'px';
                                                                if (this.offsetHeight < window.innerHeight + 100)
                                                                {
                                                                    this.style.width = null;
                                                                    this.style.height = (window.innerHeight + 100) + 'px';
                                                                }
                                                                ImagePositions[0].x = -50;
                                                                ImagePositions[0].y = -50;
                                                            }
                                                            else
                                                            {
                                                                this.style.width = window.innerWidth + 'px';
                                                                if (this.offsetHeight < window.innerHeight)
                                                                {
                                                                    this.style.width = null;
                                                                    this.style.height = window.innerHeight + 'px';
                                                                }
                                                                ImagePositions[0].x = 0;
                                                                ImagePositions[0].y = 0;
                                                            }
                                                            CurrentImageTranslation = 0;
                                                        };
            document.getElementById('Image2').onload = function() {
                                                            if (MakeAnimation)
                                                            {
                                                                this.style.width = (window.innerWidth + 100) + 'px';
                                                                if (this.offsetHeight < window.innerHeight + 100)
                                                                {
                                                                    this.style.width = null;
                                                                    this.style.height = (window.innerHeight + 100) + 'px';
                                                                }
                                                                ImagePositions[1].x = -50;
                                                                ImagePositions[1].y = -50;
                                                            }
                                                            else
                                                            {
                                                                this.style.width = window.innerWidth + 'px';
                                                                if (this.offsetHeight < window.innerHeight)
                                                                {
                                                                    this.style.width = null;
                                                                    this.style.height = window.innerHeight + 'px';
                                                                }
                                                                ImagePositions[0].x = 0;
                                                                ImagePositions[0].y = 0;
                                                            }
                                                            CurrentImageTranslation = 0;
                                                        };
            window.setInterval(function() {
                                    ImageTimeout = ImageTimeout - 1;
                                    if (ImageTimeout <= 0)
                                    {
                                        ImageTimeout = 10;
                                        CurrentImage = CurrentImage + 1;
                                        if (CurrentImage >= Images.length)
                                         {CurrentImage = 0;}
                                        if (CurrentIndex == 1)
                                         {CurrentIndex = 2;}
                                        else
                                         {CurrentIndex = 1;}
                                        document.getElementById('Image' + CurrentIndex).style.width = null;
                                        document.getElementById('Image' + CurrentIndex).style.height = null;
                                        document.getElementById('Image' + CurrentIndex).src = Images[CurrentImage];
                                    }
                                }, 1000);
                                window.setInterval(function() {
                                                        if (MakeAnimation)
                                                        {
                                                            for (var i = 0; i < 2; ++i)
                                                            {
                                                                ImagePositions[i].x = ImagePositions[i].x + ImagePositions[i].px;
                                                                ImagePositions[i].y = ImagePositions[i].y + ImagePositions[i].py;
                                                                var OffWidth = document.getElementById('Image' + (i + 1)).offsetWidth;
                                                                var OffHeight = document.getElementById('Image' + (i + 1)).offsetHeight;
                                                                if (ImagePositions[i].x >= 0 || ImagePositions[i].x + OffWidth <= window.innerWidth)
                                                                 {ImagePositions[i].px = -ImagePositions[i].px;}
                                                                if (ImagePositions[i].y >= 0 || ImagePositions[i].y + OffHeight <= window.innerHeight)
                                                                 {ImagePositions[i].py = -ImagePositions[i].py;}
                                                                document.getElementById('Image' + (i + 1)).style.left = ImagePositions[i].x + 'px';
                                                                document.getElementById('Image' + (i + 1)).style.top = ImagePositions[i].y + 'px';
                                                            }
                                                        }
                                                        if (CurrentImageTranslation > -1)
                                                        {
                                                            var DoReset = false;
                                                            CurrentImageTranslation = CurrentImageTranslation + 0.025;
                                                            if (CurrentImageTranslation >= 1.0)
                                                            {
                                                                CurrentImageTranslation = 1.0;
                                                                DoReset = true;
                                                            }
                                                            if (CurrentIndex == 1)
                                                            {
                                                                document.getElementById('Image1').style.opacity = CurrentImageTranslation;
                                                                document.getElementById('Image2').style.opacity = 1 - CurrentImageTranslation;
                                                            }
                                                            else
                                                            {
                                                                document.getElementById('Image2').style.opacity = CurrentImageTranslation;
                                                                document.getElementById('Image1').style.opacity = 1 - CurrentImageTranslation;
                                                            }
                                                            if (DoReset)
                                                             {CurrentImageTranslation = -1;}
                                                        }
                                                    }, 50);
        </script>
        <div class='InfoBox' style='position: absolute; right: 50px; top: 50px;'>
            <div class='InfoBoxTitle'><?= $v['grid-name'] ?></div>
            Willkommen<br />
            Bitte melde dich an, um <?= $v['grid-name'] ?> zu betreten.<br />
            <br />

            <?= $v['news'] ?>
        </div>
        <div class='InfoBox' style='position: absolute; left: 50px; bottom: 50px;'>
            <div class='InfoBoxTitle'>
                Status: <span style='color: rgb(0, 255, 0);'>Online</span>
            </div>
            <?= $v['stats'] ?>
        </div>
    </body>
</html>
