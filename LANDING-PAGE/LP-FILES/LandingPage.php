<?php
include 'LP-Header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="../LP-CSS/scroll-animation.css">
  <link rel="stylesheet" href="../LP-CSS/LandingPage.css">
  <link rel="stylesheet" href="../../INCLUDES/general-CSS.css">
  <!-- Font Awesome Free CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">

</head>

<body>
  <main class="container-xxl p-0">

    <! --==========START OF HERO SECTION==========-->
      <section class="lp-hero-sec position-relative" id="hero">
        <img class="main-bg img-fluid w-100" src="../../INCLUDES/LP-IMAGES/awegreen-bg.jpg" alt="A We Green Banner">
        <div class="hero-sec-container position-absolute top-0 start-0 w-100 h-100 text-light d-flex flex-column align-items-center justify-content-center text-center gap-2">

          <h1 class="green-text ">Building a Sustainable Future, One Project at a Time</h1>
          <p>A We Green Enterprise offers comprehensive solutions in security systems, renewable energy, and interior design to transform your spaces.</p>
          <button class="btn green-bg btn-started text-light">Get Started</button>
        </div>
      </section>
      <! --==========END OF HERO SECTION==========-->

        <! --==========START OF ABOUT US SECTION==========-->
          <section class="lp-about-sec" id="about-us">
            <div class="section-container">
              <div class="lp-title-con text-center pb-5">
                <h2 class="green-text">About Us</h2>
                <p>Building tomorrow's sustainable solutions today</p>
              </div>

              <div class="row">
                <div class="col-md-6 pe-4 animate-left-on-scroll">
                  <h3 class="awe-history green-text">A We Green Enterprise - Your Partner in Sustainable Innovation</h3>
                  <p class="mt-4 light-text">Founded with a vision to create a greener, more sustainable future, We Green Enterprise has been at the forefront of delivering comprehensive solutions in security systems, renewable energy, and interior design for over a decade. <br> <br>

                    Our team of certified professionals combines technical expertise with creative innovation to transform spaces and empower businesses and homeowners with cutting-edge technology. We believe in quality, sustainability, and building lasting relationships with our clients.
                  </p>

                  <div class="d-flex mt-5">
                    <div class="w-50">
                      <h4 class="text-success mb-0 about-stats">500+</h4>
                      <p class="light-text">Projects Completed</p>
                    </div>
                    <div class="w-50">
                      <h4 class="text-success mb-0 about-stats">98%</h4>
                      <p class="light-text">Client Satisfaction</p>
                    </div>
                  </div>
                </div>

                <div class="col-md-6 animate-right-on-scroll">
                  <div class="d-flex h-100 justify-content-center align-items-center gap-3 img-container">
                    <div class="about-img-con mt-4">
                      <img class="img-fluid rounded mb-3" src="../../INCLUDES/LP-IMAGES/solar-project.jpg" alt="">
                      <img class="img-fluid rounded" src="../../INCLUDES/LP-IMAGES/cctv-project.jpg" alt="">
                    </div>
                    <div class="about-img-con -mt-4">
                      <img class="about-img img-fluid rounded  mb-3" src="../../INCLUDES/LP-IMAGES/ceiling-project.webp" alt="">
                      <img class="manlift-img img-fluid rounded object-fit-cover object" src="../../INCLUDES/LP-IMAGES/cctv-project-2.jpg" alt="">
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </section>
          <! --==========END OF ABOUT US SECTION==========-->

            <! --==========START OF SERVICES SECTION==========-->
              <section class="lp-services-sec light-bg" id="services">
                <div class="section-container">
                  <div class="lp-title-con text-center pb-5">
                    <h2 class="green-text">Our Services</h2>
                    <p>Building tomorrow's sustainable solutions today</p>
                  </div>
                  <div class="row services-container animate-on-scroll">
                    <div class="col-md-4">

                      <div class="services-con border rounded-3 p-4 bg-white">
                        <div class="services-icon-container rounded bg-success flex ">
                          <i class="fas fa-video text-white fs-24"></i>
                        </div>
                        <p class="services-title fs-20 fw-semibold mt-3 mb-2">CCTV Installation</p>
                        <p class="services-text light-text mb-0">Professional security camera systems with 24/7 monitoring capabilities and advanced analytics.</p>
                      </div>

                    </div>
                    <div class="col-md-4">
                      <div class="services-con border rounded-3 p-4 bg-white">
                        <div class="services-icon-container rounded bg-success flex ">
                          <i class="fas fa-sun text-white fs-24"></i>
                        </div>
                        <p class="services-title fs-20 fw-semibold mt-3 mb-2">Solar Panel Installation</p>
                        <p class="services-text light-text mb-0">Eco-friendly and reliable solar solutions to efficiently reduce energy costs and promote sustainable living.</p>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="services-con border rounded-3 p-4 bg-white">
                        <div class="services-icon-container rounded bg-success flex ">
                          <i class="fas fa-hammer text-white fs-24"></i>
                        </div>
                        <p class="services-title fs-20 fw-semibold mt-3 mb-2">Room Renovation</p>
                        <p class="services-text light-text mb-0">Complete interior transformation services from initial design to flawless execution with premium quality finishes.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
              <! --==========END OF SERVICES SECTION==========-->

                <! --==========START OF RECENT PROJECTS SECTION==========-->
                  <section class="lp-projects-sec" id="projects">
                    <div class="section-container">
                      <div class="lp-title-con text-center pb-5">
                        <h2 class="green-text">Our Recent Projects</h2>
                        <p class="w-60 mx-auto">See how we've helped businesses and homeowners achieve their goals with our expert solutions</p>
                      </div>
                      <div class="row projects-container animate-on-scroll">
                        <div class="col-md-4">
                          <div class="card">
                            <div class="projects-img-con position-relative overflow-hidden">
                              <img src="../../INCLUDES/LP-IMAGES/center-island-cctv.jpg" class="card-img-top" alt="...">
                              <p class="position-absolute start-0 top-0 bg-success rounded-pill px-3 py-1 text-white fs-14 m-3">CCTV Installation</p>
                            </div>
                            <div class="card-body">
                              <h5 class="card-title green-text">Center Island CCTV </h5>
                              <p class="card-text fs-14">Complete surveillance solution for a corporate facility with 32 high-definition cameras and advanced monitoring.</p>

                              <div class="divider mb-3"></div>

                              <div class="row">
                                <div class="col-8">
                                  <p class="text-muted fs-12 mb-0">24 Cameras • 4K Resolution • 30-Day Storage</p>
                                </div>
                                <div class="col-4 flex">
                                  <a href="#" class="btn fs-14 flex gap-1 view-details-btn">
                                    <span class="view-details">View Details </span>
                                    <i class="fas fa-arrow-right ps-1"></i>
                                  </a>
                                </div>
                              </div>

                            </div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="card">
                            <div class="projects-img-con position-relative overflow-hidden">
                              <img src="../../INCLUDES/LP-IMAGES/20-panel-solar.jpg" class="card-img-top" alt="...">
                              <p class="position-absolute start-0 top-0 bg-success rounded-pill px-3 py-1 text-white fs-14 m-3">Solar Panel Installation</p>
                            </div>
                            <div class="card-body">
                              <h5 class="card-title green-text">Residential Solar Installation</h5>
                              <p class="card-text fs-14">20-panel solar system reducing energy costs and providing sustainable energy for the home.</p>

                              <div class="divider mb-3"></div>

                              <div class="row">
                                <div class="col-8">
                                  <p class="text-muted fs-12 mb-0">20 Panels • Renewable Energy • ROI in 6 Years</p>
                                </div>
                                <div class="col-4">
                                  <a href="#" class="btn fs-14 p-0 flex gap-1 view-details-btn d-inline-block">
                                    <span class="view-details">View Details </span>
                                    <i class="fas fa-arrow-right ps-1"></i>
                                  </a>
                                </div>
                              </div>

                            </div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="card projects-card">
                            <div class="projects-img-con position-relative overflow-hidden">
                              <img src="../../INCLUDES/LP-IMAGES/wpc-ceiling.webp" class="card-img-top img-fluid" alt="...">
                              <p class="position-absolute start-0 top-0 bg-success rounded-pill px-3 py-1 text-white fs-14 m-3">Room Renovation</p>
                            </div>
                            <div class="card-body">
                              <h5 class="card-title green-text">WPC Ceiling Installation</h5>
                              <p class="card-text fs-14">Modern and eco-friendly ceiling installation using durable Wood-Plastic Composite materials.</p>

                              <div class="divider mb-3"></div>

                              <div class="row">
                                <div class="col-8">
                                  <p class="text-muted fs-12 mb-0">WPC Material • Eco-Friendly • Modern Style</p>
                                </div>
                                <div class="col-4">
                                  <a href="#" class="btn fs-14 p-0 flex gap-1 view-details-btn d-inline-block">
                                    <span class="view-details">View Details </span>
                                    <i class="fas fa-arrow-right ps-1"></i>
                                  </a>
                                </div>
                              </div>

                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </section>
                  <! --==========END RECENT PROJECTS SECTION==========-->

                    <! --==========START OF CONTACT SECTION==========-->
                      <section class="lp-contact-sec green-bg" id="contact-us">
                        <div class="section-container">
                          <div class="lp-title-con text-center pb-5">
                            <h2 class="text-light">Ready to Start Your Project?</h2>
                            <p class="text-light">Get in touch with our team today and let us bring your vision to life</p>
                          </div>
                          <div class="row services-container">
                            <div class="col-md-6 animate-left-on-scroll">

                              <div class="contact-con border rounded-3 p-4 bg-white">
                                <h2 class="fs-24">Send Us a Message</h2>
                                <div class="mb-3">
                                  <label for="f-name" class="form-label fs-14">Full Name *</label>
                                  <input type="text" class="form-control fs-14" id="f-name" placeholder="(Optional)">
                                </div>

                                <div class="mb-3">
                                  <label for="email" class="form-label fs-14">Email *</label>
                                  <input type="email" class="form-control fs-14" id="email" placeholder="you@example.com">
                                </div>

                                <div class="mb-3">
                                  <label for="subject" class="form-label fs-14">Subject *</label>
                                  <input type="text" class="form-control fs-14" id="subject" placeholder="What can we help you with? ">
                                </div>

                                <div class="mb-3">
                                  <label for="message" class="form-label fs-14">Message *</label>
                                  <textarea class="form-control fs-14" id="message" rows="4" placeholder="Tell us about your project..."></textarea>
                                </div>

                                <div class="d-grid gap-2 mt-3">
                                  <button class="btn green-bg text-white fs-14 btn-send" type="button">Send Message</button>
                                  <button class="btn request-btn btn-white border fs-14 request-quotation-btn" type="button">Request Quotation</button>
                                </div>
                              </div>

                            </div>
                            <div class="col-md-6 animate-right-on-scroll">
                              <div class="flex h-100">
                                <div class="p-0 gap-3">

                                  <div class="hikvision-container position-relative mb-3 overflow-hidden rounded-3 border border-secondary">
                                    <img class="img-fluid hikvision-logo w-100" src="../../INCLUDES/LP-IMAGES/hikvision-logo.png" alt="hikvision logo">
                                    <div class="position-absolute bottom-0 start-0 h-100 w-100 d-flex flex-column justify-content-end ps-4">
                                      <p class="mb-0 fs-20 fw-bold">Powered with Hikvision</p>
                                      <p class="fs-14">Global leaders in smart surveillance technology</p>
                                    </div>
                                  </div>

                                  <div class="row gap-3">
                                    <div class="col pe-0">
                                      <div class="card overflow-hidden">
                                        <img src="../../INCLUDES/LP-IMAGES/seminar.webp" class="card-img-top contact-img img-fluid" alt="...">
                                        <div class="card-body">
                                          <p class="card-text mb-0 fw-semibold">Professional Excellence</p>
                                          <p class="card-text fs-14 light-text">Certified technicians delivering quality results</p>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col ps-0">
                                      <div class="card overflow-hidden">
                                        <img src="../../INCLUDES/LP-IMAGES/warranty.webp" class="card-img-top contact-img img-fluid" alt="...">
                                        <div class="card-body">
                                          <p class="card-text mb-0 fw-semibold">Warranty Guarantee</p>
                                          <p class="card-text fs-14 light-text">All of our installations and services come with a reliable warranty.</p>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </section>
                      <! --==========END OF CONTACT SECTION==========-->

                        <footer class="footer light-bg ">
                          <div class="row section-container">
                            <div class="col-md-3">
                              <div class="d-flex flex-column align-items-start">
                                <a href="#" class="fw-bold text-start text-decoration-none flex gap-1 fs-18">
                                  <img class="awegreen-logo-footer" src="../../INCLUDES/LP-IMAGES/awegreen-logo.png" alt="A We Green Logo" />
                                  <span class="green-text ">A We Green Enterprise</span>
                                </a>
                                <p class="mt-2 fs-14 light-text">Building a sustainable future through innovative green solutions and professional service excellence.</p>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="d-flex flex-column">
                                <p class="fw-500">Quick Links</p>
                                <div class="d-flex flex-column gap-2">
                                  <a href="#" class="nav-link fs-14 light-text">Home</a>
                                  <a href="#about-us" class="nav-link fs-14 light-text">About</a>
                                  <a href="#services" class="nav-link fs-14 light-text">Services</a>
                                  <a href="#choose-us" class="nav-link fs-14 light-text">Choose Us</a>
                                  <a href="#projects" class="nav-link fs-14 light-text">Projects</a>
                                </div>
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="d-flex flex-column gap-2">
                                <p>Our Services</p>
                                <p class="fs-14 mb-0 light-text">CCTV Installation</p>
                                <p class="fs-14 mb-0 light-text">Solar Panel Installation</p>
                                <p class="fs-14 mb-0 light-text">Room Renovation</p>

                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="d-flex flex-column gap-2">
                                <p>Contact Us</p>
                                <p class="fs-14 mb-0 light-text"><i class="fa-solid fa-envelope me-1"></i> awegreenenterprise@gmail.com</p>
                                <p class="fs-14 mb-0 light-text"><i class="fa-solid fa-phone me-1"></i>
                                  Globe: 0917 752 3343 Smart: 0998 884 5671</p>

                                <p class="fs-14 mb-0 light-text"><i class="fa-solid fa-location-dot me-1"></i>
                                  Main Office: ATH Phase 4 Blk 51 lot 30 Brgy. A. Olaes, GMA, Cavite 4117</p>
                                <p class="fs-14 mb-0 light-text"><i class="fa-solid fa-location-dot me-1"></i>
                                  Satellite Office: ATH Phase 5 Blk 14 lot 5 Brgy. F. de Castro, GMA, Cavite 4117</p>
                              </div>
                            </div>
                          </div>
                          <div class="section-container">
                            <div class="divider my-5"></div>
                          </div>
                          <p class="text-center fs-14 light-text mx-auto">© 2025 We Green Enterprise. All rights reserved.</p>
                        </footer>
  </main>

  <script src="scroll&header.js"></script>

</body>

</html>