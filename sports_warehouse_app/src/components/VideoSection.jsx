import React, { useRef, useEffect, useState } from 'react';
import { Box } from '@mui/material';

const VideoSection = ({ onVideoEnd }) => {
  const introVideoRef = useRef(null);
  const mainVideoRef = useRef(null);
  const [mainVideoVisible, setMainVideoVisible] = useState(false);

  useEffect(() => {
    const introVideo = introVideoRef.current;
    const mainVideo = mainVideoRef.current;

    if (introVideo && mainVideo) {
      // When the intro video ends, hide it, show the main video, and play the main video
      const handleIntroEnded = () => {
        introVideo.style.display = 'none';
        mainVideo.style.display = 'block';
        setMainVideoVisible(true);
        mainVideo.play();
      };

      // When the intro video starts playing, ensure main video starts loading
      const handleIntroPlaying = () => {
        mainVideo.load();
      };

      // Log when the main video is buffered and ready
      const handleMainCanPlayThrough = () => {
        console.log("Video 2 buffered and ready");
      };

      // When main video metadata is loaded, show captions if available
      const handleMainLoadedMetadata = () => {
        if (mainVideo.textTracks && mainVideo.textTracks[0]) {
          mainVideo.textTracks[0].mode = 'showing';
        }
      };

      // When the main video ends, trigger the onVideoEnd callback
      const handleMainEnded = () => {
        if (onVideoEnd) {
          onVideoEnd();
        }
      };

      // Attach event listeners
      introVideo.addEventListener('ended', handleIntroEnded);
      introVideo.addEventListener('playing', handleIntroPlaying);
      mainVideo.addEventListener('canplaythrough', handleMainCanPlayThrough);
      mainVideo.addEventListener('loadedmetadata', handleMainLoadedMetadata);
      mainVideo.addEventListener('ended', handleMainEnded);

      // Cleanup event listeners on unmount
      return () => {
        introVideo.removeEventListener('ended', handleIntroEnded);
        introVideo.removeEventListener('playing', handleIntroPlaying);
        mainVideo.removeEventListener('canplaythrough', handleMainCanPlayThrough);
        mainVideo.removeEventListener('loadedmetadata', handleMainLoadedMetadata);
        mainVideo.removeEventListener('ended', handleMainEnded);
      };
    }
  }, [onVideoEnd]);

  return (
    <Box
      sx={{
        maxWidth: '800px',
        margin: '0 auto',
        padding: '10px',
        backgroundColor: 'background.paper',
        border: 2,
        borderColor: 'grey.300',
        borderRadius: '8px',
        overflow: 'hidden',
      }}
    >
      {/* Intro Video: "Coming Soon" */}
      <video
        ref={introVideoRef}
        id="introVideo"
        src="/images/videos/coming-soon-neon.mp4"
        preload="auto"
        autoPlay
        muted
        playsInline
        style={{
          width: '100%',
          height: 'auto',
          display: 'block',
          aspectRatio: '16/9',
          objectFit: 'contain',
          borderRadius: '10px',
          marginBottom: '1rem',
        }}
      />
      
      {/* Main Video: "Quick High-Five" (hidden initially) */}
      <video
        ref={mainVideoRef}
        id="mainVideo"
        src="/images/videos/quick-high-five.mp4"
        preload="auto"
        playsInline
        style={{
          width: '100%',
          height: 'auto',
          display: mainVideoVisible ? 'block' : 'none',
          aspectRatio: '16/9',
          objectFit: 'contain',
          borderRadius: '10px',
          marginBottom: '1rem',
        }}
      >
        <track
          default
          kind="captions"
          src="/images/videos/highfive-captions.vtt"
          srclang="en"
          label="English"
        />
      </video>
    </Box>
  );
};

export default VideoSection;

