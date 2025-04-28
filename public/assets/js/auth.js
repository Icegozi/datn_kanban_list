$(document).ready(function () {
  const videoBg = `
    <div id="video-background-wrapper">
      <video autoplay muted loop playsinline id="bg-video">
        <source src="${window.bgVideoUrl}" type="video/mp4">
        Trình duyệt của bạn không hỗ trợ video nền.
      </video>
      <div id="video-overlay"></div>
    </div>
  `;

  $('body').prepend(videoBg);

  const styles = `
    #video-background-wrapper {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      overflow: hidden;
      z-index: -1;
    }

    #bg-video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(0.5);
    }

    #video-overlay {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.25);
    }

    .register-page {
      position: relative;
      z-index: 1;
      background-color: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(2px);
      border-radius: 10px;
      padding: 20px;
    }
  `;
  $('<style>').prop('type', 'text/css').html(styles).appendTo('head');
});
