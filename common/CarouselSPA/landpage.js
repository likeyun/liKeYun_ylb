const slider = document.getElementById('slider');
const textContainer = document.getElementById('sliderText');
const dotsContainer = document.getElementById('dots');
const copyButton = document.getElementById('copyButton');

const urlParams = new URLSearchParams(window.location.search);
const carousel_id_fromURL = urlParams.get('carousel_id');
    
if (!carousel_id_fromURL) {
    const errorMessage = document.createElement('div');
    errorMessage.classList.add('error-message');
    errorMessage.innerHTML = '<div id="warnning"><img src="../../../static/img/warn.png" /></div><p id="warnText">URL参数为空!</p>';
    document.body.appendChild(errorMessage);
    document.getElementById('sliderText').style.display = 'none';
}else {
    
    // 使用fetch来获取接口数据
    fetch('getCarouselInfo.php?carousel_id='+carousel_id_fromURL)
    .then(response => response.json())
    .then(data => {
        
        // 判断是否上传了轮播图
        if(data.carousel_datas.carousel_pics.length > 0) {
            
            // 请求正常
            if (data.code === 0) {
                const images = data.carousel_datas.carousel_pics;
                const titles = data.carousel_datas.carousel_info;
                document.title = data.carousel_datas.carousel_info[0]['Carousel_title'];
                function createSlides() {
                    images.forEach((image, index) => {
                        const slide = document.createElement('div');
                        slide.classList.add('slide');
                        slide.dataset.index = index;
                        slide.innerHTML = `<img src="${image.pic_url}" alt="图片${index + 1}">`;
                        slider.appendChild(slide);
                        
                        const dot = document.createElement('div');
                        dot.classList.add('dot');
                        if (index === 0) dot.classList.add('active');
                        dot.dataset.index = index;
                        dot.addEventListener('click', () => {
                            slide.scrollIntoView({ behavior: 'smooth' });
                        });
                        dotsContainer.appendChild(dot);
                        
                        // 复制按钮显示状态
                        if (image.show_copy_btn == 2) { // 显示按钮
                            copyButton.style.display = 'block';
                        }
                    });
                    textContainer.innerHTML = images[0].pic_desc;
                }
    
                function updateActiveDot() {
                    let currentIndex = Math.round(slider.scrollLeft / slider.clientWidth);
                    textContainer.innerHTML = images[currentIndex].pic_desc;
                    
                    // 检查当前图片是否显示复制按钮
                    if (images[currentIndex].show_copy_btn == 1) {
                        copyButton.style.display = 'block'; // 显示按钮
                    } else {
                        copyButton.style.display = 'none'; // 隐藏按钮
                    }
    
                    document.querySelectorAll('.dot').forEach((dot, index) => {
                        dot.classList.toggle('active', index === currentIndex);
                    });
                }
    
                slider.addEventListener('scroll', () => {
                    clearTimeout(slider.scrollEndTimeout);
                    slider.scrollEndTimeout = setTimeout(updateActiveDot, 150);
                });
    
                copyButton.addEventListener('click', () => {
                    const textToCopy = textContainer.textContent;
                    navigator.clipboard.writeText(textToCopy).then(() => {
                        copyButton.textContent = "已复制";
                        setTimeout(() => {
                            copyButton.textContent = "一键复制";
                        }, 2000);
                    }).catch(err => {
                        console.error("复制失败", err);
                    });
                });
    
                createSlides(); // 创建轮播图
            } else { // 如果接口返回的 code 不是 0，显示错误信息
                
                // 错误提示
                const errorMessage = document.createElement('div');
                errorMessage.classList.add('error-message'); // 添加自定义的 class
                document.getElementById('sliderText').style.display = 'none';
                errorMessage.innerHTML = '<div id="warnning"><img src="../../../static/img/warn.png" /></div><p id="warnText">'+data.msg+'</p>';
                
                // 将错误信息添加到页面中
                document.body.appendChild(errorMessage);
            }
        }else {
            
            // 未上传轮播图
            const errorMessage = document.createElement('div');
            errorMessage.classList.add('error-message'); // 添加自定义的 class
            document.getElementById('sliderText').style.display = 'none';
            errorMessage.innerHTML = '<div id="warnning"><img src="../../../static/img/warn.png" /></div><p id="warnText">暂无内容</p>';
            document.body.appendChild(errorMessage);
            document.querySelector('.dots').style.display = 'none';
        }
    })
    .catch(err => {
        console.error("接口请求失败", err);
        
        // 错误提示
        const errorMessage = document.createElement('div');
        errorMessage.classList.add('error-message'); // 添加自定义的 class
        document.getElementById('sliderText').style.display = 'none';
        errorMessage.innerHTML = '<div id="warnning"><img src="../../../static/img/warn.png" /></div><p id="warnText">网络请求失败，可能是服务器接口问题</p>';
        
        // 将错误信息添加到页面中
        document.body.appendChild(errorMessage);
    });
}